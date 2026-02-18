<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use GuzzleHttp\Client;

class TiktokController extends Controller
{
    public function index()
    {
        return view('tiktok.index');
    }

    public function download(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        $key = 'tiktok-download:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            return back()->with('error', 'Too many requests. Please wait a moment.');
        }

        RateLimiter::hit($key, 60); // 10 attempts per minute

        $videoUrl = $request->input('url');

        try {
            $result = $this->fetchVideoData($videoUrl);

            if (!$result) {
                return back()->with('error', 'Could not fetch video. Please check the URL.');
            }

            return view('tiktok.index', ['video' => $result]);

        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    private function fetchVideoData(string $url): ?array
    {
        $client = new Client(['timeout' => 30]);

        // Using a public TikTok API service (no-watermark)
        $apiUrl = 'https://tikwm.com/api/';

        $response = $client->post($apiUrl, [
            'form_params' => [
                'url'  => $url,
                'hd'   => 1,
            ],
            'headers' => [
                'User-Agent' => 'Mozilla/5.0',
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        if ($data['code'] !== 0) {
            return null;
        }

        return [
            'title'       => $data['data']['title'] ?? 'TikTok Video',
            'cover'       => $data['data']['cover'] ?? null,
            'play'        => $data['data']['play'] ?? null,      // no watermark
            'hdplay'      => $data['data']['hdplay'] ?? null,    // HD no watermark
            'author'      => $data['data']['author']['nickname'] ?? 'Unknown',
            'duration'    => $data['data']['duration'] ?? 0,
        ];
    }

    public function stream(Request $request)
    {
        $videoUrl = $request->query('url');

        if (!$videoUrl) {
            abort(400, 'No URL provided');
        }

        $client = new Client(['timeout' => 60]);
        $response = $client->get($videoUrl, ['stream' => true]);

        return response()->stream(function () use ($response) {
            $body = $response->getBody();
            while (!$body->eof()) {
                echo $body->read(1024);
                flush();
            }
        }, 200, [
            'Content-Type'        => 'video/mp4',
            'Content-Disposition' => 'attachment; filename="tiktok_' . time() . '.mp4"',
        ]);
    }
}
