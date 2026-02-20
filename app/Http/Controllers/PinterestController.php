<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use GuzzleHttp\Client;
use App\Models\DownloadLog;

class PinterestController extends Controller
{
    public function index()
    {
        return view('pinterest.index');
    }

    public function download(Request $request)
    {
        $request->validate([
            'url' => ['required', 'url', function ($attribute, $value, $fail) {
                if (!preg_match('/pinterest\.com\/pin\/|pin\.it\//i', $value)) {
                    $fail('Please enter a valid Pinterest pin URL.');
                }
            }],
        ]);

        $key = 'pinterest-download:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            return back()->with('error', 'Too many requests. Please wait a moment.');
        }

        RateLimiter::hit($key, 60);

        $videoUrl = $request->input('url');

        try {
            $result = $this->fetchVideoData($videoUrl);

            if (!$result) {
                return back()->with('error', 'Could not fetch video. Make sure this is a video pin and the URL is correct.');
            }

            DownloadLog::logFetch('pinterest', $videoUrl, $result['title'] ?? null, $request);

            return view('pinterest.index', ['video' => $result]);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return back()->with('error', 'Could not access the pin. Please check the URL.');
        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    private function fetchVideoData(string $url): ?array
    {
        // Resolve short URLs (pin.it) to full pinterest.com URLs
        $url = $this->resolveRedirects($url);

        // Extract pin ID from URL
        if (!preg_match('/\/pin\/(\d+)/i', $url, $match)) {
            return null;
        }
        $pinId = $match[1];

        // Method 1: Try JSON-LD from page HTML (fastest, most reliable)
        $result = $this->extractFromPageHtml($url, $pinId);
        if ($result) {
            return $result;
        }

        // Method 2: Try Pinterest internal API with session cookies
        return $this->extractFromApi($url, $pinId);
    }

    /**
     * Extract video from page HTML using JSON-LD and script data.
     */
    private function extractFromPageHtml(string $url, string $pinId): ?array
    {
        $client = new Client([
            'timeout' => 30,
            'verify'  => false,
        ]);

        $response = $client->get($url, [
            'headers' => [
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ],
        ]);

        $html = (string) $response->getBody();

        if (empty($html)) {
            return null;
        }

        $videoUrl = null;
        $thumbnailUrl = null;
        $title = 'Pinterest Video';
        $description = '';

        // Try JSON-LD VideoObject
        if (preg_match_all('/<script[^>]*type="application\/ld\+json"[^>]*>(.*?)<\/script>/si', $html, $ldScripts)) {
            foreach ($ldScripts[1] as $ldStr) {
                $ld = json_decode($ldStr, true);
                if (!$ld) continue;

                if (($ld['@type'] ?? '') === 'VideoObject') {
                    $videoUrl = $ld['contentUrl'] ?? null;
                    $thumbnailUrl = $ld['thumbnailUrl'] ?? null;
                    $title = $ld['name'] ?? $title;
                    $description = $ld['description'] ?? '';
                    break;
                }
            }
        }

        // Fallback: search for 720p MP4 in scripts
        if (!$videoUrl) {
            if (preg_match('/https?:\/\/v1\.pinimg\.com\/videos\/[^"\'\\s]*720p[^"\'\\s]*\.mp4/i', $html, $m)) {
                $videoUrl = stripcslashes($m[0]);
            }
        }

        // Fallback: any pinimg MP4
        if (!$videoUrl) {
            if (preg_match('/https?:\/\/v1\.pinimg\.com\/videos\/[^"\'\\s]*\.mp4/i', $html, $m)) {
                $videoUrl = stripcslashes($m[0]);
            }
        }

        if (!$videoUrl) {
            return null;
        }

        // Get thumbnail from og:image if not found
        if (!$thumbnailUrl && preg_match('/property="og:image"\s+content="([^"]+)"/i', $html, $m)) {
            $thumbnailUrl = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
        }

        // Get description from og:description
        if (!$description && preg_match('/property="og:description"\s+content="([^"]+)"/i', $html, $m)) {
            $description = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
        }

        return [
            'title'       => $title ?: 'Pinterest Video',
            'description' => $description,
            'cover'       => $thumbnailUrl,
            'video'       => $videoUrl,
        ];
    }

    /**
     * Extract video data from Pinterest internal API using session cookies.
     */
    private function extractFromApi(string $url, string $pinId): ?array
    {
        $cookieJar = tempnam(sys_get_temp_dir(), 'pin_');

        try {
            // Step 1: Get cookies + CSRF token from the page
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING       => '',
                CURLOPT_HEADER         => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                CURLOPT_COOKIEJAR      => $cookieJar,
                CURLOPT_COOKIEFILE     => $cookieJar,
            ]);
            $fullResp = curl_exec($ch);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = substr($fullResp, 0, $headerSize);
            curl_close($ch);

            // Extract CSRF token
            $csrfToken = '';
            if (preg_match('/csrftoken=([^;]+)/i', $headers, $m)) {
                $csrfToken = $m[1];
            }

            if (!$csrfToken) {
                return null;
            }

            // Step 2: Call PinResource API
            $ch = curl_init();
            $dataParam = json_encode([
                'options' => ['id' => $pinId, 'field_set_key' => 'detailed'],
                'context' => new \stdClass(),
            ]);
            $apiUrl = 'https://www.pinterest.com/resource/PinResource/get/?' . http_build_query([
                'source_url' => "/pin/$pinId/",
                'data'       => $dataParam,
            ]);
            curl_setopt_array($ch, [
                CURLOPT_URL            => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING       => '',
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                CURLOPT_COOKIEFILE     => $cookieJar,
                CURLOPT_HTTPHEADER     => [
                    'Accept: application/json',
                    'X-Requested-With: XMLHttpRequest',
                    'X-CSRFToken: ' . $csrfToken,
                    'X-Pinterest-AppState: active',
                    'X-Pinterest-Source-Url: /pin/' . $pinId . '/',
                    'X-Pinterest-PWS-Handler: www/pin/[id].js',
                    'Referer: https://www.pinterest.com/pin/' . $pinId . '/',
                ],
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code !== 200) {
                return null;
            }

            $data = json_decode($resp, true);
            $pin = $data['resource_response']['data'] ?? null;

            if (!$pin) {
                return null;
            }

            // Get video URL from video_list
            $videoUrl = null;
            if (isset($pin['videos']['video_list'])) {
                $videoList = $pin['videos']['video_list'];
                // Prefer 720P mp4
                if (isset($videoList['V_720P']['url'])) {
                    $videoUrl = $videoList['V_720P']['url'];
                } else {
                    // Take first available mp4 format
                    foreach ($videoList as $fmt => $info) {
                        if (isset($info['url']) && str_contains($info['url'], '.mp4')) {
                            $videoUrl = $info['url'];
                            break;
                        }
                    }
                }
            }

            // Check story pin pages for video
            if (!$videoUrl && isset($pin['story_pin_data']['pages'])) {
                foreach ($pin['story_pin_data']['pages'] as $page) {
                    foreach ($page['blocks'] ?? [] as $block) {
                        if (isset($block['video']['video_list'])) {
                            $vl = $block['video']['video_list'];
                            $videoUrl = $vl['V_720P']['url'] ?? ($vl['V_ENC_720P']['url'] ?? null);
                            if ($videoUrl) break 2;
                        }
                    }
                }
            }

            if (!$videoUrl) {
                return null;
            }

            // Get cover image
            $cover = $pin['images']['474x']['url'] ?? ($pin['images']['orig']['url'] ?? null);

            return [
                'title'       => $pin['title'] ?: ($pin['grid_title'] ?: 'Pinterest Video'),
                'description' => $pin['description'] ?? '',
                'cover'       => $cover,
                'video'       => $videoUrl,
            ];

        } finally {
            @unlink($cookieJar);
        }
    }

    /**
     * Follow redirects to resolve short URLs (pin.it).
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

        DownloadLog::logDownload('pinterest', $request);

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
            'Content-Disposition' => 'attachment; filename="pinterest_' . time() . '.mp4"',
        ]);
    }
}
