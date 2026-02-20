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

    /**
     * Fetch video data using yt-dlp.
     */
    private function fetchVideoData(string $url): ?array
    {
        $escapedUrl = escapeshellarg($url);
        $cmd = "python -m yt_dlp --js-runtimes nodejs -j {$escapedUrl} 2>NUL";
        $output = shell_exec($cmd);

        if (!$output) {
            return null;
        }

        $data = json_decode($output, true);
        if (!$data || !isset($data['title'])) {
            return null;
        }

        $title = mb_substr($data['title'] ?? 'YouTube Video', 0, 200);
        $author = $data['uploader'] ?? $data['channel'] ?? 'Unknown';
        $duration = $this->formatDuration((int) ($data['duration'] ?? 0));
        $thumbnail = $data['thumbnail'] ?? null;
        $videoId = $data['id'] ?? '';

        // Use highest quality thumbnail
        if (!empty($data['thumbnails'])) {
            $thumbs = array_filter($data['thumbnails'], fn($t) => isset($t['url']));
            if (!empty($thumbs)) {
                $thumbnail = end($thumbs)['url'];
            }
        }

        // Collect downloadable mp4 formats
        $formats = [];
        $seenQualities = [];

        foreach ($data['formats'] ?? [] as $fmt) {
            if (($fmt['ext'] ?? '') !== 'mp4') continue;
            if (($fmt['vcodec'] ?? 'none') === 'none') continue;
            if (!isset($fmt['url'])) continue;

            $quality = $fmt['format_note'] ?? $fmt['qualityLabel'] ?? '?';
            $hasAudio = ($fmt['acodec'] ?? 'none') !== 'none';
            $resolution = $fmt['resolution'] ?? '?';
            $size = $this->getFormatSize($fmt);
            $vcodec = $fmt['vcodec'] ?? '';
            $isAvc = strpos($vcodec, 'avc1') !== false;

            // Dedupe: prefer avc1 for compatibility
            $qualityKey = $quality . ($hasAudio ? '_av' : '_v');
            if (isset($seenQualities[$qualityKey]) && !$isAvc) {
                continue;
            }
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

        // Split into combined and video-only
        $combinedFormats = array_values(array_filter($formats, fn($f) => $f['hasAudio']));
        $videoOnlyFormats = array_values(array_filter($formats, function ($f) {
            return !$f['hasAudio'] && $this->qualityOrder($f['quality']) >= $this->qualityOrder('480p');
        }));

        // Sort by quality descending
        usort($combinedFormats, fn($a, $b) => $this->qualityOrder($b['quality']) - $this->qualityOrder($a['quality']));
        usort($videoOnlyFormats, fn($a, $b) => $this->qualityOrder($b['quality']) - $this->qualityOrder($a['quality']));

        if (empty($combinedFormats) && empty($videoOnlyFormats)) {
            return null;
        }

        return [
            'title'     => $title,
            'author'    => $author,
            'duration'  => $duration,
            'cover'     => $thumbnail,
            'videoId'   => $videoId,
            'combined'  => $combinedFormats,
            'videoOnly' => $videoOnlyFormats,
        ];
    }

    private function getFormatSize(array $fmt): string
    {
        $bytes = $fmt['filesize'] ?? $fmt['filesize_approx'] ?? 0;
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

        $contentType = $response->getHeaderLine('Content-Type') ?: 'video/mp4';
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
