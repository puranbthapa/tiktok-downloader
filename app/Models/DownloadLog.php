<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadLog extends Model
{
    protected $fillable = [
        'platform',
        'source_url',
        'video_title',
        'ip_address',
        'country',
        'user_agent',
        'referer',
        'downloaded',
    ];

    protected $casts = [
        'downloaded' => 'boolean',
    ];

    /**
     * Log a video fetch (when user submits a URL).
     */
    public static function logFetch(string $platform, string $sourceUrl, ?string $title, $request): self
    {
        return self::create([
            'platform'    => $platform,
            'source_url'  => $sourceUrl,
            'video_title' => $title ? mb_substr($title, 0, 250) : $title,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'referer'     => $request->header('referer'),
            'downloaded'  => false,
        ]);
    }

    /**
     * Mark that the video was actually downloaded (stream hit).
     */
    public static function logDownload(string $platform, $request): void
    {
        // Find the most recent fetch log for this IP + platform and mark it downloaded
        self::where('platform', $platform)
            ->where('ip_address', $request->ip())
            ->where('downloaded', false)
            ->latest()
            ->first()
            ?->update(['downloaded' => true]);
    }
}
