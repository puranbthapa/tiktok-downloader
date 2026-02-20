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

        // Method 1: Invidious API instances (most reliable from server IPs)
        $result = $this->fetchViaInvidious($videoId);
        if ($result) return $result;

        // Method 2: Piped API instances
        $result = $this->fetchViaPiped($videoId);
        if ($result) return $result;

        // Method 3: Cobalt API
        $result = $this->fetchViaCobalt($videoId);
        if ($result) return $result;

        // Method 4: yt-dlp fallback (if available)
        $result = $this->fetchViaYtDlp($url);
        if ($result) return $result;

        return null;
    }

    /* ------------------------------------------------------------------
     *  Extract the 11-char video ID
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
     *  Method 1: Invidious API
     *  Open-source YouTube proxy with public instances.
     *  API docs: https://docs.invidious.io/api/
     * ----------------------------------------------------------------*/
    private function fetchViaInvidious(string $videoId): ?array
    {
        // Updated list of working Invidious instances (Feb 2026)
        // Check https://api.invidious.io/ for current list
        $instances = [
            'https://invidious.nerdvpn.de',
            'https://inv.nadeko.net',
            'https://invidious.jing.rocks',
            'https://invidious.privacydev.net',
            'https://invidious.drgns.space',
            'https://yt.drgnz.club',
            'https://iv.melmac.space',
            'https://invidious.perennialte.ch',
        ];

        // Shuffle to distribute load
        shuffle($instances);

        $client = new Client(['timeout' => 10, 'verify' => false, 'connect_timeout' => 5]);

        foreach ($instances as $instance) {
            try {
                $apiUrl = "{$instance}/api/v1/videos/{$videoId}?fields=title,author,lengthSeconds,videoThumbnails,adaptiveFormats,formatStreams";

                $response = $client->get($apiUrl, [
                    'http_errors' => false,
                    'headers'     => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Accept'     => 'application/json',
                    ],
                ]);

                if ($response->getStatusCode() !== 200) {
                    \Log::info("YouTube Invidious ({$instance}): HTTP {$response->getStatusCode()} for {$videoId}");
                    continue;
                }

                $data = json_decode($response->getBody(), true);
                if (!$data || !isset($data['title'])) {
                    \Log::info("YouTube Invidious ({$instance}): no data for {$videoId}");
                    continue;
                }

                $result = $this->parseInvidiousResponse($data, $videoId);
                if ($result) {
                    \Log::info("YouTube: fetched via Invidious ({$instance}) for {$videoId}");
                    return $result;
                }

            } catch (\Exception $e) {
                \Log::info("YouTube Invidious ({$instance}) failed for {$videoId}: " . $e->getMessage());
                continue;
            }
        }

        return null;
    }

    /**
     * Parse Invidious API response.
     */
    private function parseInvidiousResponse(array $data, string $videoId): ?array
    {
        $title    = mb_substr($data['title'] ?? 'YouTube Video', 0, 200);
        $author   = $data['author'] ?? 'Unknown';
        $duration = $this->formatDuration((int) ($data['lengthSeconds'] ?? 0));

        // Get best thumbnail
        $thumbnail = null;
        if (!empty($data['videoThumbnails'])) {
            foreach ($data['videoThumbnails'] as $thumb) {
                if (($thumb['quality'] ?? '') === 'maxresdefault' || ($thumb['quality'] ?? '') === 'sddefault') {
                    $thumbnail = $thumb['url'] ?? null;
                    break;
                }
            }
            if (!$thumbnail) {
                $thumbnail = $data['videoThumbnails'][0]['url'] ?? null;
            }
        }
        if (!$thumbnail) {
            $thumbnail = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
        }

        $formats       = [];
        $seenQualities = [];

        // Combined formats (formatStreams = audio+video muxed)
        foreach ($data['formatStreams'] ?? [] as $fmt) {
            $container = $fmt['container'] ?? '';
            if (strtolower($container) !== 'mp4') continue;

            $url = $fmt['url'] ?? null;
            if (!$url) continue;

            $quality    = $fmt['qualityLabel'] ?? $fmt['quality'] ?? '?';
            $resolution = $fmt['resolution'] ?? ($fmt['size'] ?? '?');
            $size       = '';

            $qualityKey = $quality . '_av';
            if (isset($seenQualities[$qualityKey])) continue;
            $seenQualities[$qualityKey] = true;

            $formats[] = [
                'url'        => $url,
                'quality'    => $quality,
                'resolution' => $resolution,
                'formatId'   => (string) ($fmt['itag'] ?? ''),
                'size'       => $size,
                'hasAudio'   => true,
            ];
        }

        // Adaptive formats (video-only or audio-only)
        foreach ($data['adaptiveFormats'] ?? [] as $fmt) {
            $type = $fmt['type'] ?? '';
            if (strpos($type, 'video/mp4') === false) continue;

            $url = $fmt['url'] ?? null;
            if (!$url) continue;

            $quality    = $fmt['qualityLabel'] ?? $fmt['quality'] ?? '?';
            $hasAudio   = !empty($fmt['audioQuality']);
            $resolution = $fmt['resolution'] ?? '?';
            $size       = $this->formatBytes((int) ($fmt['clen'] ?? $fmt['contentLength'] ?? 0));
            $isAvc      = strpos($type, 'avc1') !== false;

            $qualityKey = $quality . ($hasAudio ? '_av' : '_v');
            if (isset($seenQualities[$qualityKey]) && !$isAvc) continue;
            $seenQualities[$qualityKey] = true;

            $formats[] = [
                'url'        => $url,
                'quality'    => $quality,
                'resolution' => $resolution,
                'formatId'   => (string) ($fmt['itag'] ?? ''),
                'size'       => $size,
                'hasAudio'   => $hasAudio,
            ];
        }

        return $this->splitAndSortFormats($formats, $title, $author, $duration, $thumbnail, $videoId);
    }

    /* ------------------------------------------------------------------
     *  Method 2: Piped API
     *  Another open-source YouTube proxy.
     *  Docs: https://docs.piped.video/docs/api-documentation/
     * ----------------------------------------------------------------*/
    private function fetchViaPiped(string $videoId): ?array
    {
        // Updated list of working Piped instances (Feb 2026)
        // Check https://piped-instances.kavin.rocks/ for current list
        $instances = [
            'https://pipedapi.kavin.rocks',
            'https://pipedapi.tokhmi.xyz',
            'https://pipedapi.moomoo.me',
            'https://pipedapi.syncpundit.io',
            'https://api-piped.mha.fi',
            'https://piped-api.garuber.com',
        ];

        // Shuffle to distribute load
        shuffle($instances);

        $client = new Client(['timeout' => 10, 'verify' => false, 'connect_timeout' => 5]);

        foreach ($instances as $instance) {
            try {
                $apiUrl = "{$instance}/streams/{$videoId}";

                $response = $client->get($apiUrl, [
                    'http_errors' => false,
                    'headers'     => [
                        'User-Agent' => 'Mozilla/5.0',
                        'Accept'     => 'application/json',
                    ],
                ]);

                if ($response->getStatusCode() !== 200) {
                    \Log::info("YouTube Piped ({$instance}): HTTP {$response->getStatusCode()} for {$videoId}");
                    continue;
                }

                $data = json_decode($response->getBody(), true);
                if (!$data || !isset($data['title'])) {
                    \Log::info("YouTube Piped ({$instance}): no data for {$videoId}");
                    continue;
                }

                $result = $this->parsePipedResponse($data, $videoId);
                if ($result) {
                    \Log::info("YouTube: fetched via Piped ({$instance}) for {$videoId}");
                    return $result;
                }

            } catch (\Exception $e) {
                \Log::info("YouTube Piped ({$instance}) failed for {$videoId}: " . $e->getMessage());
                continue;
            }
        }

        return null;
    }

    /**
     * Parse Piped API response.
     */
    private function parsePipedResponse(array $data, string $videoId): ?array
    {
        $title     = mb_substr($data['title'] ?? 'YouTube Video', 0, 200);
        $author    = $data['uploader'] ?? $data['uploaderName'] ?? 'Unknown';
        $duration  = $this->formatDuration((int) ($data['duration'] ?? 0));
        $thumbnail = $data['thumbnailUrl'] ?? "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";

        $formats       = [];
        $seenQualities = [];

        // Video streams (can have audio)
        foreach ($data['videoStreams'] ?? [] as $fmt) {
            $mime = $fmt['mimeType'] ?? $fmt['format'] ?? '';
            if (strpos($mime, 'video/mp4') === false && ($fmt['format'] ?? '') !== 'MPEG_4') continue;

            $url = $fmt['url'] ?? null;
            if (!$url) continue;

            $quality    = $fmt['quality'] ?? '?';
            $hasAudio   = ($fmt['videoOnly'] ?? true) === false;
            $w          = $fmt['width'] ?? 0;
            $h          = $fmt['height'] ?? 0;
            $resolution = ($w && $h) ? "{$w}x{$h}" : '?';
            $size       = $this->formatBytes((int) ($fmt['contentLength'] ?? 0));
            $codec      = $fmt['codec'] ?? '';
            $isAvc      = strpos($codec, 'avc1') !== false;

            $qualityKey = $quality . ($hasAudio ? '_av' : '_v');
            if (isset($seenQualities[$qualityKey]) && !$isAvc) continue;
            $seenQualities[$qualityKey] = true;

            $formats[] = [
                'url'        => $url,
                'quality'    => $quality,
                'resolution' => $resolution,
                'formatId'   => (string) ($fmt['itag'] ?? ''),
                'size'       => $size,
                'hasAudio'   => $hasAudio,
            ];
        }

        return $this->splitAndSortFormats($formats, $title, $author, $duration, $thumbnail, $videoId);
    }

    /* ------------------------------------------------------------------
     *  Method 3: Cobalt API
     *  Open-source media downloader API.
     * ----------------------------------------------------------------*/
    private function fetchViaCobalt(string $videoId): ?array
    {
        $instances = [
            'https://api.cobalt.tools',
            'https://cobalt-api.hyper.lol',
        ];

        $client = new Client(['timeout' => 15, 'verify' => false, 'connect_timeout' => 5]);

        foreach ($instances as $instance) {
            try {
                $response = $client->post("{$instance}/", [
                    'http_errors' => false,
                    'json'        => [
                        'url'            => "https://www.youtube.com/watch?v={$videoId}",
                        'downloadMode'   => 'auto',
                        'filenameStyle'  => 'basic',
                    ],
                    'headers' => [
                        'User-Agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Content-Type' => 'application/json',
                        'Accept'       => 'application/json',
                    ],
                ]);

                if ($response->getStatusCode() !== 200) {
                    \Log::info("YouTube Cobalt ({$instance}): HTTP {$response->getStatusCode()} for {$videoId}");
                    continue;
                }

                $data = json_decode($response->getBody(), true);
                if (!$data || empty($data['url'])) {
                    \Log::info("YouTube Cobalt ({$instance}): no url in response for {$videoId}");
                    continue;
                }

                // Cobalt returns a single download URL — build simple result
                $thumbnail = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";

                // Get title from YouTube oEmbed (lightweight)
                $title  = 'YouTube Video';
                $author = 'Unknown';
                try {
                    $oembed = $client->get("https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v={$videoId}&format=json", [
                        'http_errors' => false,
                        'timeout'     => 5,
                    ]);
                    if ($oembed->getStatusCode() === 200) {
                        $oData  = json_decode($oembed->getBody(), true);
                        $title  = mb_substr($oData['title'] ?? $title, 0, 200);
                        $author = $oData['author_name'] ?? $author;
                        if (!empty($oData['thumbnail_url'])) {
                            $thumbnail = $oData['thumbnail_url'];
                        }
                    }
                } catch (\Exception $e) {
                    // oEmbed failed, use defaults
                }

                \Log::info("YouTube: fetched via Cobalt ({$instance}) for {$videoId}");

                return [
                    'title'     => $title,
                    'author'    => $author,
                    'duration'  => '0:00',
                    'cover'     => $thumbnail,
                    'videoId'   => $videoId,
                    'combined'  => [
                        [
                            'url'        => $data['url'],
                            'quality'    => 'Best',
                            'resolution' => '?',
                            'formatId'   => '',
                            'size'       => '',
                            'hasAudio'   => true,
                        ],
                    ],
                    'videoOnly' => [],
                ];

            } catch (\Exception $e) {
                \Log::info("YouTube Cobalt ({$instance}) failed for {$videoId}: " . $e->getMessage());
                continue;
            }
        }

        return null;
    }

    /* ------------------------------------------------------------------
     *  Method 4: yt-dlp fallback (cross-platform)
     * ----------------------------------------------------------------*/
    private function fetchViaYtDlp(string $url): ?array
    {
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
