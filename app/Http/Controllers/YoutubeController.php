<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use GuzzleHttp\Client;
use App\Models\DownloadLog;

class YoutubeController extends Controller
{
    public function index()
    {
        return view('youtube.index');
    }

    public function download(Request $request)
    {
        $request->validate([
            'url' => ['required', 'url', function ($attribute, $value, $fail) {
                if (!preg_match('/youtube\.com|youtu\.be/i', $value)) {
                    $fail('Please enter a valid YouTube URL.');
                }
            }],
        ]);

        $key = 'youtube-download:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            return back()->with('error', 'Too many requests. Please wait a moment.');
        }

        RateLimiter::hit($key, 60);

        $videoUrl = $request->input('url');

        try {
            $result = $this->fetchVideoData($videoUrl);

            if (!$result) {
                return back()->with('error', 'Could not fetch video. Make sure the video is public and not age-restricted.');
            }

            DownloadLog::logFetch('youtube', $videoUrl, $result['title'] ?? null, $request);

            return view('youtube.index', ['video' => $result]);

        } catch (\Exception $e) {
            \Log::error('YouTube Exception [' . get_class($e) . ']: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return back()->with('error', 'Something went wrong. Please try again or check that the video is publicly available.');
        }
    }

    /* ------------------------------------------------------------------
     *  MAIN FETCH — tries multiple methods in order
     * ----------------------------------------------------------------*/
    private function fetchVideoData(string $url): ?array
    {
        $videoId = $this->extractVideoId($url);

        if (!$videoId) {
            \Log::warning('YouTube: Could not extract video ID from URL: ' . $url);
            return null;
        }

        // Method 1: YouTube innertube API (works on any server, no dependencies)
        $result = $this->fetchViaInnertubeApi($videoId);
        if ($result) return $result;

        // Method 2: Scrape YouTube page for ytInitialPlayerResponse
        $result = $this->fetchViaScraping($videoId);
        if ($result) return $result;

        // Method 3: yt-dlp fallback (if installed on server)
        $result = $this->fetchViaYtDlp($url);
        if ($result) return $result;

        return null;
    }

    /* ------------------------------------------------------------------
     *  Extract the 11-char video ID from various YouTube URL formats
     * ----------------------------------------------------------------*/
    private function extractVideoId(string $url): ?string
    {
        $patterns = [
            '/(?:youtube\.com\/watch\?.*v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/.*[?&]v=([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    /* ------------------------------------------------------------------
     *  Method 1: YouTube innertube API (ANDROID / IOS clients)
     *  These clients return direct MP4 URLs without signature issues.
     * ----------------------------------------------------------------*/
    private function fetchViaInnertubeApi(string $videoId): ?array
    {
        $client = new Client(['timeout' => 30, 'verify' => false]);

        $clients = [
            [
                'name'    => 'ANDROID',
                'version' => '19.09.37',
                'ua'      => 'com.google.android.youtube/19.09.37 (Linux; U; Android 11) gzip',
                'extra'   => ['androidSdkVersion' => 30, 'osName' => 'Android', 'osVersion' => '11'],
                'cnum'    => '3',
            ],
            [
                'name'    => 'IOS',
                'version' => '19.09.3',
                'ua'      => 'com.google.ios.youtube/19.09.3 (iPhone14,3; U; CPU iOS 15_6 like Mac OS X)',
                'extra'   => ['deviceModel' => 'iPhone14,3', 'osName' => 'iPhone', 'osVersion' => '15.6'],
                'cnum'    => '5',
            ],
        ];

        foreach ($clients as $cfg) {
            try {
                $payload = [
                    'videoId' => $videoId,
                    'context' => [
                        'client' => array_merge([
                            'clientName'    => $cfg['name'],
                            'clientVersion' => $cfg['version'],
                            'hl' => 'en',
                            'gl' => 'US',
                        ], $cfg['extra']),
                    ],
                    'playbackContext' => [
                        'contentPlaybackContext' => [
                            'html5Preference' => 'HTML5_PREF_WANTS',
                        ],
                    ],
                    'contentCheckOk' => true,
                    'racyCheckOk'    => true,
                ];

                $response = $client->post('https://www.youtube.com/youtubei/api/v1/player', [
                    'json'    => $payload,
                    'headers' => [
                        'User-Agent'               => $cfg['ua'],
                        'Content-Type'             => 'application/json',
                        'X-YouTube-Client-Name'    => $cfg['cnum'],
                        'X-YouTube-Client-Version' => $cfg['version'],
                        'Origin'                   => 'https://www.youtube.com',
                    ],
                ]);

                $data = json_decode($response->getBody(), true);
                if (!$data) continue;

                $status = $data['playabilityStatus']['status'] ?? 'UNPLAYABLE';
                if ($status !== 'OK') {
                    \Log::info("YouTube innertube ({$cfg['name']}): status={$status} for {$videoId}");
                    continue;
                }

                $result = $this->parseInnertubeResponse($data, $videoId);
                if ($result) {
                    \Log::info("YouTube: fetched via innertube ({$cfg['name']}) for {$videoId}");
                    return $result;
                }
            } catch (\Exception $e) {
                \Log::warning("YouTube innertube ({$cfg['name']}) failed for {$videoId}: " . $e->getMessage());
                continue;
            }
        }

        return null;
    }

    /* ------------------------------------------------------------------
     *  Parse innertube API player response into our standard format
     * ----------------------------------------------------------------*/
    private function parseInnertubeResponse(array $data, string $videoId): ?array
    {
        $details   = $data['videoDetails'] ?? [];
        $title     = mb_substr($details['title'] ?? 'YouTube Video', 0, 200);
        $author    = $details['author'] ?? 'Unknown';
        $duration  = $this->formatDuration((int) ($details['lengthSeconds'] ?? 0));
        $thumbs    = $details['thumbnail']['thumbnails'] ?? [];
        $thumbnail = !empty($thumbs) ? end($thumbs)['url'] : null;

        $streaming  = $data['streamingData'] ?? [];
        $allFormats = array_merge(
            $streaming['formats'] ?? [],
            $streaming['adaptiveFormats'] ?? []
        );

        $formats       = [];
        $seenQualities = [];

        foreach ($allFormats as $fmt) {
            $mime = $fmt['mimeType'] ?? '';
            if (strpos($mime, 'video/mp4') === false) continue;

            // Only use formats with a direct URL (skip cipher/signatureCipher)
            $url = $fmt['url'] ?? null;
            if (!$url) continue;

            $quality    = $fmt['qualityLabel'] ?? ($fmt['quality'] ?? '?');
            $hasAudio   = !empty($fmt['audioQuality']) || !empty($fmt['audioSampleRate']);
            $w          = $fmt['width'] ?? 0;
            $h          = $fmt['height'] ?? 0;
            $resolution = ($w && $h) ? "{$w}x{$h}" : '?';
            $size       = $this->formatBytes((int) ($fmt['contentLength'] ?? 0));
            $isAvc      = strpos($mime, 'avc1') !== false;

            $qualityKey = $quality . ($hasAudio ? '_av' : '_v');
            if (isset($seenQualities[$qualityKey]) && !$isAvc) continue;
            $seenQualities[$qualityKey] = true;

            $formats[$qualityKey] = [
                'url'        => $url,
                'quality'    => $quality,
                'resolution' => $resolution,
                'formatId'   => (string) ($fmt['itag'] ?? ''),
                'size'       => $size,
                'hasAudio'   => $hasAudio,
            ];
        }

        $formats = array_values($formats);

        return $this->splitAndSortFormats($formats, $title, $author, $duration, $thumbnail, $videoId);
    }

    /* ------------------------------------------------------------------
     *  Method 2: Scrape YouTube watch page for ytInitialPlayerResponse
     * ----------------------------------------------------------------*/
    private function fetchViaScraping(string $videoId): ?array
    {
        try {
            $client = new Client(['timeout' => 30, 'verify' => false]);

            $response = $client->get("https://www.youtube.com/watch?v={$videoId}", [
                'headers' => [
                    'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ],
            ]);

            $html = (string) $response->getBody();

            // Extract ytInitialPlayerResponse JSON
            if (!preg_match('/var\s+ytInitialPlayerResponse\s*=\s*(\{.+?\})\s*;/s', $html, $m)) {
                \Log::info("YouTube scraping: ytInitialPlayerResponse not found for {$videoId}");
                return null;
            }

            $data = json_decode($m[1], true);
            if (!$data) {
                \Log::warning("YouTube scraping: failed to decode ytInitialPlayerResponse for {$videoId}");
                return null;
            }

            $status = $data['playabilityStatus']['status'] ?? 'UNPLAYABLE';
            if ($status !== 'OK') {
                \Log::info("YouTube scraping: status={$status} for {$videoId}");
                return null;
            }

            $result = $this->parseInnertubeResponse($data, $videoId);
            if ($result) {
                \Log::info("YouTube: fetched via page scraping for {$videoId}");
            }
            return $result;

        } catch (\Exception $e) {
            \Log::warning("YouTube scraping failed for {$videoId}: " . $e->getMessage());
            return null;
        }
    }

    /* ------------------------------------------------------------------
     *  Method 3: yt-dlp fallback (cross-platform)
     * ----------------------------------------------------------------*/
    private function fetchViaYtDlp(string $url): ?array
    {
        // Check if shell_exec is available
        $disabled = array_map('trim', explode(',', ini_get('disable_functions') ?: ''));
        if (!function_exists('shell_exec') || in_array('shell_exec', $disabled)) {
            \Log::info('YouTube: shell_exec is disabled, skipping yt-dlp fallback');
            return null;
        }

        $escapedUrl  = escapeshellarg($url);
        $nullRedirect = PHP_OS_FAMILY === 'Windows' ? '2>NUL' : '2>/dev/null';

        // Try several common yt-dlp invocations
        $commands = [
            "yt-dlp -j {$escapedUrl} {$nullRedirect}",
            "python3 -m yt_dlp -j {$escapedUrl} {$nullRedirect}",
            "python -m yt_dlp -j {$escapedUrl} {$nullRedirect}",
        ];

        $output = null;
        foreach ($commands as $cmd) {
            $output = @shell_exec($cmd);
            if ($output) break;
        }

        if (!$output) {
            \Log::info('YouTube: yt-dlp returned no output');
            return null;
        }

        $data = json_decode($output, true);
        if (!$data || !isset($data['title'])) return null;

        $title     = mb_substr($data['title'] ?? 'YouTube Video', 0, 200);
        $author    = $data['uploader'] ?? $data['channel'] ?? 'Unknown';
        $duration  = $this->formatDuration((int) ($data['duration'] ?? 0));
        $thumbnail = $data['thumbnail'] ?? null;
        $videoId   = $data['id'] ?? '';

        if (!empty($data['thumbnails'])) {
            $thumbs = array_filter($data['thumbnails'], fn($t) => isset($t['url']));
            if (!empty($thumbs)) {
                $thumbnail = end($thumbs)['url'];
            }
        }

        $formats       = [];
        $seenQualities = [];

        foreach ($data['formats'] ?? [] as $fmt) {
            if (($fmt['ext'] ?? '') !== 'mp4') continue;
            if (($fmt['vcodec'] ?? 'none') === 'none') continue;
            if (!isset($fmt['url'])) continue;

            $quality  = $fmt['format_note'] ?? $fmt['qualityLabel'] ?? '?';
            $hasAudio = ($fmt['acodec'] ?? 'none') !== 'none';
            $resolution = $fmt['resolution'] ?? '?';
            $size     = $this->formatBytes((int) ($fmt['filesize'] ?? $fmt['filesize_approx'] ?? 0));
            $vcodec   = $fmt['vcodec'] ?? '';
            $isAvc    = strpos($vcodec, 'avc1') !== false;

            $qualityKey = $quality . ($hasAudio ? '_av' : '_v');
            if (isset($seenQualities[$qualityKey]) && !$isAvc) continue;
            $seenQualities[$qualityKey] = true;

            $formats[$qualityKey] = [
                'url'        => $fmt['url'],
                'quality'    => $quality,
                'resolution' => $resolution,
                'formatId'   => $fmt['format_id'] ?? '',
                'size'       => $size,
                'hasAudio'   => $hasAudio,
            ];
        }

        $formats = array_values($formats);

        \Log::info("YouTube: fetched via yt-dlp for {$videoId}");
        return $this->splitAndSortFormats($formats, $title, $author, $duration, $thumbnail, $videoId);
    }

    /* ------------------------------------------------------------------
     *  Shared helpers
     * ----------------------------------------------------------------*/

    /**
     * Split formats into combined / video-only, sort, and build final array.
     */
    private function splitAndSortFormats(array $formats, string $title, string $author, string $duration, ?string $cover, string $videoId): ?array
    {
        $combinedFormats  = array_values(array_filter($formats, fn($f) => $f['hasAudio']));
        $videoOnlyFormats = array_values(array_filter($formats, function ($f) {
            return !$f['hasAudio'] && $this->qualityOrder($f['quality']) >= $this->qualityOrder('480p');
        }));

        usort($combinedFormats,  fn($a, $b) => $this->qualityOrder($b['quality']) - $this->qualityOrder($a['quality']));
        usort($videoOnlyFormats, fn($a, $b) => $this->qualityOrder($b['quality']) - $this->qualityOrder($a['quality']));

        if (empty($combinedFormats) && empty($videoOnlyFormats)) {
            return null;
        }

        return [
            'title'     => $title,
            'author'    => $author,
            'duration'  => $duration,
            'cover'     => $cover,
            'videoId'   => $videoId,
            'combined'  => $combinedFormats,
            'videoOnly' => $videoOnlyFormats,
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) return '';
        if ($bytes < 1024 * 1024) return round($bytes / 1024) . ' KB';
        return round($bytes / (1024 * 1024), 1) . ' MB';
    }

    private function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) return '0:00';
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;
        return $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
    }

    private function qualityOrder(string $quality): int
    {
        $map = [
            '144p' => 1, '240p' => 2, '360p' => 3, '480p' => 4,
            '720p' => 5, '720p60' => 6, '1080p' => 7, '1080p60' => 8,
            '1440p' => 9, '2160p' => 10, '4320p' => 11,
        ];
        return $map[$quality] ?? 0;
    }

    /**
     * Stream / proxy the video download.
     */
    public function stream(Request $request)
    {
        $videoUrl = $request->query('url');

        if (!$videoUrl) {
            abort(400, 'No URL provided');
        }

        DownloadLog::logDownload('youtube', $request);

        $client = new Client([
            'timeout' => 300,
            'verify'  => false,
        ]);

        $response = $client->get($videoUrl, [
            'stream'  => true,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ],
        ]);

        $contentType   = $response->getHeaderLine('Content-Type') ?: 'video/mp4';
        $contentLength = $response->getHeaderLine('Content-Length');

        $headers = [
            'Content-Type'        => $contentType,
            'Content-Disposition' => 'attachment; filename="youtube_' . time() . '.mp4"',
        ];

        if ($contentLength) {
            $headers['Content-Length'] = $contentLength;
        }

        return response()->stream(function () use ($response) {
            $body = $response->getBody();
            while (!$body->eof()) {
                echo $body->read(8192);
                flush();
            }
        }, 200, $headers);
    }
}
