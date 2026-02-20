<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use GuzzleHttp\Client;
use App\Models\DownloadLog;

class YoutubeController extends Controller
{
    /**
     * YouTube innertube public API key (embedded in YouTube's own JS).
     */
    private const INNERTUBE_API_KEY = 'AIzaSyAO_FJ2SlqU8Q4STEHLGCilw_Y9_11qcW8';

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

            // Wrap DB logging in try-catch so DB issues don't break the page
            try {
                DownloadLog::logFetch('youtube', $videoUrl, $result['title'] ?? null, $request);
            } catch (\Exception $dbEx) {
                \Log::warning('YouTube: DB log failed: ' . $dbEx->getMessage());
            }

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

        // Method 1: Embedded player innertube (bypasses login requirement)
        $result = $this->fetchViaEmbeddedPlayer($videoId);
        if ($result) return $result;

        // Method 2: Innertube API with WEB client + consent cookies
        $result = $this->fetchViaWebClient($videoId);
        if ($result) return $result;

        // Method 3: Scrape YouTube embed page
        $result = $this->fetchViaScraping($videoId);
        if ($result) return $result;

        // Method 4: yt-dlp fallback (if installed on server)
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
     *  Method 1: TVHTML5_SIMPLY_EMBEDDED_PLAYER client
     *  This client is used for embedded videos and bypasses login/age
     *  restrictions. Uses the correct /youtubei/v1/player endpoint
     *  with the public innertube API key.
     * ----------------------------------------------------------------*/
    private function fetchViaEmbeddedPlayer(string $videoId): ?array
    {
        try {
            $client = new Client(['timeout' => 30, 'verify' => false]);

            $payload = [
                'videoId' => $videoId,
                'context' => [
                    'client' => [
                        'clientName'    => 'TVHTML5_SIMPLY_EMBEDDED_PLAYER',
                        'clientVersion' => '2.0',
                        'hl'            => 'en',
                        'gl'            => 'US',
                    ],
                    'thirdParty' => [
                        'embedUrl' => 'https://www.youtube.com',
                    ],
                ],
                'playbackContext' => [
                    'contentPlaybackContext' => [
                        'html5Preference'  => 'HTML5_PREF_WANTS',
                        'signatureTimestamp' => 20073,
                    ],
                ],
                'contentCheckOk' => true,
                'racyCheckOk'    => true,
            ];

            $apiUrl = 'https://www.youtube.com/youtubei/v1/player?key=' . self::INNERTUBE_API_KEY;

            $response = $client->post($apiUrl, [
                'json'        => $payload,
                'http_errors' => false,
                'headers'     => [
                    'User-Agent'   => 'Mozilla/5.0 (ChromiumStylePlatform) Cobalt/Version',
                    'Content-Type' => 'application/json',
                    'Origin'       => 'https://www.youtube.com',
                    'Referer'      => "https://www.youtube.com/embed/{$videoId}",
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                \Log::info("YouTube embedded player: HTTP {$statusCode} for {$videoId}");
                return null;
            }

            $data = json_decode($response->getBody(), true);
            if (!$data) return null;

            $status = $data['playabilityStatus']['status'] ?? 'UNPLAYABLE';
            if ($status !== 'OK') {
                \Log::info("YouTube embedded player: status={$status} for {$videoId}");
                return null;
            }

            $result = $this->parseInnertubeResponse($data, $videoId);
            if ($result) {
                \Log::info("YouTube: fetched via embedded player for {$videoId}");
            }
            return $result;

        } catch (\Exception $e) {
            \Log::warning("YouTube embedded player failed for {$videoId}: " . $e->getMessage());
            return null;
        }
    }

    /* ------------------------------------------------------------------
     *  Method 2: WEB client with consent cookies
     *  Sets SOCS consent cookie to bypass EU login-wall.
     * ----------------------------------------------------------------*/
    private function fetchViaWebClient(string $videoId): ?array
    {
        try {
            $client = new Client(['timeout' => 30, 'verify' => false]);

            $payload = [
                'videoId' => $videoId,
                'context' => [
                    'client' => [
                        'clientName'    => 'WEB',
                        'clientVersion' => '2.20240101.00.00',
                        'hl'            => 'en',
                        'gl'            => 'US',
                    ],
                ],
                'playbackContext' => [
                    'contentPlaybackContext' => [
                        'html5Preference' => 'HTML5_PREF_WANTS',
                    ],
                ],
                'contentCheckOk' => true,
                'racyCheckOk'    => true,
            ];

            $apiUrl = 'https://www.youtube.com/youtubei/v1/player?key=' . self::INNERTUBE_API_KEY
                    . '&prettyPrint=false';

            $response = $client->post($apiUrl, [
                'json'        => $payload,
                'http_errors' => false,
                'headers'     => [
                    'User-Agent'               => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Content-Type'             => 'application/json',
                    'Origin'                   => 'https://www.youtube.com',
                    'Referer'                  => 'https://www.youtube.com/',
                    'X-YouTube-Client-Name'    => '1',
                    'X-YouTube-Client-Version' => '2.20240101.00.00',
                    'Cookie'                   => 'SOCS=CAESEwgDEgk0ODE3Nzk3MjQaAmVuIAEaBgiA_LyaBg; CONSENT=PENDING+987',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                \Log::info("YouTube WEB client: HTTP {$statusCode} for {$videoId}");
                return null;
            }

            $data = json_decode($response->getBody(), true);
            if (!$data) return null;

            $status = $data['playabilityStatus']['status'] ?? 'UNPLAYABLE';
            if ($status !== 'OK') {
                \Log::info("YouTube WEB client: status={$status} for {$videoId}");
                return null;
            }

            $result = $this->parseInnertubeResponse($data, $videoId);
            if ($result) {
                \Log::info("YouTube: fetched via WEB client for {$videoId}");
            }
            return $result;

        } catch (\Exception $e) {
            \Log::warning("YouTube WEB client failed for {$videoId}: " . $e->getMessage());
            return null;
        }
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

        if (empty($allFormats)) {
            \Log::info("YouTube innertube: no streaming formats found for {$videoId}");
            return null;
        }

        $formats       = [];
        $seenQualities = [];

        foreach ($allFormats as $fmt) {
            $mime = $fmt['mimeType'] ?? '';
            if (strpos($mime, 'video/mp4') === false) continue;

            // Use direct URL, or try to build from signatureCipher
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
     *  Method 3: Scrape YouTube watch page with consent cookies
     * ----------------------------------------------------------------*/
    private function fetchViaScraping(string $videoId): ?array
    {
        try {
            $client = new Client(['timeout' => 30, 'verify' => false]);

            // Try the embed page first (less login restriction)
            $urls = [
                "https://www.youtube.com/embed/{$videoId}",
                "https://www.youtube.com/watch?v={$videoId}",
            ];

            foreach ($urls as $pageUrl) {
                $response = $client->get($pageUrl, [
                    'http_errors' => false,
                    'headers'     => [
                        'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept-Language' => 'en-US,en;q=0.9',
                        'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                        'Cookie'          => 'SOCS=CAESEwgDEgk0ODE3Nzk3MjQaAmVuIAEaBgiA_LyaBg; CONSENT=PENDING+987',
                    ],
                ]);

                if ($response->getStatusCode() !== 200) continue;

                $html = (string) $response->getBody();

                // Try ytInitialPlayerResponse
                $data = null;
                if (preg_match('/var\s+ytInitialPlayerResponse\s*=\s*(\{.+?\})\s*;\s*(?:var|<\/script)/s', $html, $m)) {
                    $data = json_decode($m[1], true);
                }

                // Fallback: try to find embedded player response
                if (!$data && preg_match('/ytInitialPlayerResponse["\s]*[:=]\s*(\{.+?\})\s*[;,]/s', $html, $m)) {
                    $data = json_decode($m[1], true);
                }

                if (!$data) continue;

                $status = $data['playabilityStatus']['status'] ?? 'UNPLAYABLE';
                if ($status !== 'OK') {
                    \Log::info("YouTube scraping ({$pageUrl}): status={$status} for {$videoId}");
                    continue;
                }

                $result = $this->parseInnertubeResponse($data, $videoId);
                if ($result) {
                    \Log::info("YouTube: fetched via scraping ({$pageUrl}) for {$videoId}");
                    return $result;
                }
            }

            \Log::info("YouTube scraping: all page attempts failed for {$videoId}");
            return null;

        } catch (\Exception $e) {
            \Log::warning("YouTube scraping failed for {$videoId}: " . $e->getMessage());
            return null;
        }
    }

    /* ------------------------------------------------------------------
     *  Method 4: yt-dlp fallback (cross-platform)
     * ----------------------------------------------------------------*/
    private function fetchViaYtDlp(string $url): ?array
    {
        // Check if shell_exec is available
        $disabled = array_map('trim', explode(',', ini_get('disable_functions') ?: ''));
        if (!function_exists('shell_exec') || in_array('shell_exec', $disabled)) {
            \Log::info('YouTube: shell_exec is disabled, skipping yt-dlp fallback');
            return null;
        }

        $escapedUrl   = escapeshellarg($url);
        $nullRedirect = PHP_OS_FAMILY === 'Windows' ? '2>NUL' : '2>/dev/null';

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

            $quality    = $fmt['format_note'] ?? $fmt['qualityLabel'] ?? '?';
            $hasAudio   = ($fmt['acodec'] ?? 'none') !== 'none';
            $resolution = $fmt['resolution'] ?? '?';
            $size       = $this->formatBytes((int) ($fmt['filesize'] ?? $fmt['filesize_approx'] ?? 0));
            $vcodec     = $fmt['vcodec'] ?? '';
            $isAvc      = strpos($vcodec, 'avc1') !== false;

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

        try {
            DownloadLog::logDownload('youtube', $request);
        } catch (\Exception $e) {
            \Log::warning('YouTube: DB log failed on stream: ' . $e->getMessage());
        }

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
