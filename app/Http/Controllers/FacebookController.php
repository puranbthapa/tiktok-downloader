<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use GuzzleHttp\Client;
use App\Models\DownloadLog;

class FacebookController extends Controller
{
    public function index()
    {
        return view('facebook.index');
    }

    public function download(Request $request)
    {
        $request->validate([
            'url' => ['required', 'url', function ($attribute, $value, $fail) {
                if (!preg_match('/facebook\.com|fb\.watch|fb\.com/i', $value)) {
                    $fail('Please enter a valid Facebook URL.');
                }
            }],
        ]);

        $key = 'facebook-download:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            return back()->with('error', 'Too many requests. Please wait a moment.');
        }

        RateLimiter::hit($key, 60);

        $videoUrl = $request->input('url');

        try {
            $result = $this->fetchVideoData($videoUrl);

            if (!$result) {
                return back()->with('error', 'Could not fetch video. Please check the URL and make sure the video is public.');
            }

            DownloadLog::logFetch('facebook', $videoUrl, $result['title'] ?? null, $request);

            return view('facebook.index', ['video' => $result]);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            \Log::error('Facebook ClientException: ' . $e->getMessage());
            return back()->with('error', 'Could not access the video. Make sure the URL is correct and the video is public.');
        } catch (\Exception $e) {
            \Log::error('Facebook Exception [' . get_class($e) . ']: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Something went wrong. Please try again or check that the video is publicly available.');
        }
    }

    private function fetchVideoData(string $url): ?array
    {
        // Resolve fb.watch short URLs to full facebook.com URLs
        $url = $this->resolveRedirects($url);

        $client = new Client([
            'timeout' => 30,
            'verify'  => false,
        ]);

        // Scrape the Facebook page directly for video URLs
        $response = $client->get($url, [
            'headers' => [
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'sec-fetch-dest'  => 'document',
                'sec-fetch-mode'  => 'navigate',
                'sec-fetch-site'  => 'none',
                'sec-fetch-user'  => '?1',
                'upgrade-insecure-requests' => '1',
            ],
        ]);

        $html = (string) $response->getBody();

        if (empty($html)) {
            return null;
        }

        $hdUrl = null;
        $sdUrl = null;

        // Extract browser_native_hd_url and browser_native_sd_url
        if (preg_match('/"browser_native_hd_url"\s*:\s*"(https?[^"]+)"/i', $html, $match)) {
            $hdUrl = stripcslashes($match[1]);
        }
        if (preg_match('/"browser_native_sd_url"\s*:\s*"(https?[^"]+)"/i', $html, $match)) {
            $sdUrl = stripcslashes($match[1]);
        }

        // Fallback: try playable_url_quality_hd and playable_url
        if (!$hdUrl && preg_match('/"playable_url_quality_hd"\s*:\s*"(https?[^"]+)"/i', $html, $match)) {
            $hdUrl = stripcslashes($match[1]);
        }
        if (!$sdUrl && preg_match('/"playable_url"\s*:\s*"(https?[^"]+)"/i', $html, $match)) {
            $sdUrl = stripcslashes($match[1]);
        }

        if (!$hdUrl && !$sdUrl) {
            return null;
        }

        // Extract title from og:title meta tag
        $title = 'Facebook Video';
        if (preg_match('/property="og:title"\s+content="([^"]+)"/i', $html, $match)) {
            $title = html_entity_decode($match[1], ENT_QUOTES, 'UTF-8');
            // Truncate very long titles (FB descriptions can be huge)
            $title = mb_substr($title, 0, 200);
        }

        // Extract thumbnail from og:image meta tag
        $cover = null;
        if (preg_match('/property="og:image"\s+content="([^"]+)"/i', $html, $match)) {
            $cover = html_entity_decode($match[1], ENT_QUOTES, 'UTF-8');
        }

        return [
            'title'    => $title,
            'cover'    => $cover,
            'duration' => null,
            'hd'       => $hdUrl,
            'sd'       => $sdUrl,
        ];
    }

    /**
     * Follow redirects to resolve short URLs (e.g. fb.watch) to full URLs.
     */
    private function resolveRedirects(string $url): string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        curl_exec($ch);
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        return $finalUrl ?: $url;
    }

    public function stream(Request $request)
    {
        $videoUrl = $request->query('url');

        if (!$videoUrl) {
            abort(400, 'No URL provided');
        }

        DownloadLog::logDownload('facebook', $request);

        $client = new Client([
            'timeout' => 120,
            'verify'  => false,
        ]);

        $response = $client->get($videoUrl, [
            'stream'  => true,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ],
        ]);

        return response()->stream(function () use ($response) {
            $body = $response->getBody();
            while (!$body->eof()) {
                echo $body->read(4096);
                flush();
            }
        }, 200, [
            'Content-Type'        => 'video/mp4',
            'Content-Disposition' => 'attachment; filename="facebook_' . time() . '.mp4"',
        ]);
    }
}
