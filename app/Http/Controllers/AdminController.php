<?php

namespace App\Http\Controllers;

use App\Models\DownloadLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /* ───────── Auth ───────── */

    public function loginForm()
    {
        if (session('admin_authenticated')) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate(['username' => 'required', 'password' => 'required']);

        if ($request->input('username') === config('admin.username')
            && $request->input('password') === config('admin.password')) {
            session(['admin_authenticated' => true]);
            return redirect()->route('admin.dashboard');
        }

        return back()->with('error', 'Invalid credentials.')
                     ->withInput(['username' => $request->input('username')]);
    }

    public function logout()
    {
        session()->forget('admin_authenticated');
        return redirect()->route('admin.login')->with('success', 'Logged out successfully.');
    }

    /* ───────── Dashboard ───────── */

    public function dashboard(Request $request)
    {
        // — Summary cards
        $totalFetches    = DownloadLog::count();
        $totalDownloads  = DownloadLog::where('downloaded', true)->count();
        $todayFetches    = DownloadLog::whereDate('created_at', today())->count();
        $todayDownloads  = DownloadLog::whereDate('created_at', today())->where('downloaded', true)->count();
        $uniqueVisitors  = DownloadLog::distinct('ip_address')->count('ip_address');
        $conversionRate  = $totalFetches > 0 ? round(($totalDownloads / $totalFetches) * 100, 1) : 0;

        // — Per-platform
        $platformStats = DownloadLog::select(
                'platform',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN downloaded = 1 THEN 1 ELSE 0 END) as downloads')
            )
            ->groupBy('platform')
            ->orderByDesc('total')
            ->get();

        // — Daily chart (last 14 days)
        $dailyStats = DownloadLog::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as fetches'),
                DB::raw('SUM(CASE WHEN downloaded = 1 THEN 1 ELSE 0 END) as downloads')
            )
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // — Top 10 most-fetched URLs
        $topUrls = DownloadLog::select(
                'source_url', 'platform', 'video_title',
                DB::raw('COUNT(*) as hits'),
                DB::raw('SUM(CASE WHEN downloaded = 1 THEN 1 ELSE 0 END) as dl_count')
            )
            ->groupBy('source_url', 'platform', 'video_title')
            ->orderByDesc('hits')
            ->limit(10)
            ->get();

        // — Browser breakdown (top 5)
        $browsers = $this->parseBrowserStats();

        // — Recent unique visitors (last 20)
        $recentVisitors = DownloadLog::select(
                'ip_address',
                DB::raw('COUNT(*) as requests'),
                DB::raw('MAX(created_at) as last_seen'),
                DB::raw('MIN(created_at) as first_seen')
            )
            ->groupBy('ip_address')
            ->orderByDesc('last_seen')
            ->limit(20)
            ->get();

        // — Hourly distribution (today)
        $hourlyStats = DownloadLog::select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as total')
            )
            ->whereDate('created_at', today())
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        return view('admin.dashboard', compact(
            'totalFetches', 'totalDownloads', 'todayFetches', 'todayDownloads',
            'uniqueVisitors', 'conversionRate', 'platformStats', 'dailyStats',
            'topUrls', 'browsers', 'recentVisitors', 'hourlyStats'
        ));
    }

    /* ───────── Logs (separate page) ───────── */

    public function logs(Request $request)
    {
        $query = DownloadLog::query()->latest();

        if ($request->filled('platform')) {
            $query->where('platform', $request->input('platform'));
        }
        if ($request->filled('status')) {
            $query->where('downloaded', $request->input('status') === 'downloaded');
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('source_url', 'like', "%$search%")
                  ->orWhere('video_title', 'like', "%$search%")
                  ->orWhere('ip_address', 'like', "%$search%");
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->paginate(30)->appends($request->query());

        return view('admin.logs', compact('logs'));
    }

    /* ───────── Export CSV ───────── */

    public function exportCsv(Request $request)
    {
        $query = DownloadLog::query()->latest();

        if ($request->filled('platform')) {
            $query->where('platform', $request->input('platform'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->get();

        $callback = function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Platform', 'URL', 'Title', 'IP', 'User Agent', 'Downloaded', 'Date']);
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id, $log->platform, $log->source_url, $log->video_title,
                    $log->ip_address, $log->user_agent, $log->downloaded ? 'Yes' : 'No',
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="download_logs_' . date('Y-m-d') . '.csv"',
        ]);
    }

    /* ───────── Delete Actions ───────── */

    public function deleteLog($id)
    {
        DownloadLog::findOrFail($id)->delete();
        return back()->with('success', 'Log entry deleted.');
    }

    public function clearLogs(Request $request)
    {
        $request->validate(['confirm' => 'required|in:DELETE']);

        $count = DownloadLog::count();
        DownloadLog::truncate();

        return back()->with('success', "All $count log entries have been cleared.");
    }

    /* ───────── Settings ───────── */

    public function settings()
    {
        return view('admin.settings');
    }

    /* ───────── Helpers ───────── */

    private function parseBrowserStats(): \Illuminate\Support\Collection
    {
        $agents = DownloadLog::select('user_agent', DB::raw('COUNT(*) as total'))
            ->whereNotNull('user_agent')
            ->groupBy('user_agent')
            ->get();

        $browsers = [];
        foreach ($agents as $row) {
            $ua = $row->user_agent;
            $name = 'Other';
            if (str_contains($ua, 'Edg/'))          $name = 'Edge';
            elseif (str_contains($ua, 'OPR/'))       $name = 'Opera';
            elseif (str_contains($ua, 'Chrome/'))     $name = 'Chrome';
            elseif (str_contains($ua, 'Firefox/'))    $name = 'Firefox';
            elseif (str_contains($ua, 'Safari/'))     $name = 'Safari';

            $browsers[$name] = ($browsers[$name] ?? 0) + $row->total;
        }

        arsort($browsers);

        return collect(array_slice($browsers, 0, 5, true))
            ->map(fn ($total, $name) => (object)['name' => $name, 'total' => $total]);
    }
}
