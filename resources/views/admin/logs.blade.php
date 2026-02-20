@extends('admin.layout')

@section('page-title', 'Download Logs')
@section('page-subtitle', 'View and manage all download activity')

@section('content')
{{-- Filters --}}
<div class="bg-gray-900 rounded-xl p-5 border border-gray-800 mb-6">
    <form method="GET" action="{{ route('admin.logs') }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-xs text-gray-500 mb-1.5 font-medium">Platform</label>
            <select name="platform" class="bg-gray-800 text-white rounded-lg px-3 py-2 text-sm border border-gray-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none min-w-[140px]">
                <option value="">All Platforms</option>
                <option value="tiktok" {{ request('platform') === 'tiktok' ? 'selected' : '' }}>TikTok</option>
                <option value="facebook" {{ request('platform') === 'facebook' ? 'selected' : '' }}>Facebook</option>
                <option value="pinterest" {{ request('platform') === 'pinterest' ? 'selected' : '' }}>Pinterest</option>
                <option value="youtube" {{ request('platform') === 'youtube' ? 'selected' : '' }}>YouTube</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1.5 font-medium">Status</label>
            <select name="status" class="bg-gray-800 text-white rounded-lg px-3 py-2 text-sm border border-gray-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none min-w-[140px]">
                <option value="">All Status</option>
                <option value="downloaded" {{ request('status') === 'downloaded' ? 'selected' : '' }}>Downloaded</option>
                <option value="fetched" {{ request('status') === 'fetched' ? 'selected' : '' }}>Fetched Only</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1.5 font-medium">From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="bg-gray-800 text-white rounded-lg px-3 py-2 text-sm border border-gray-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1.5 font-medium">To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="bg-gray-800 text-white rounded-lg px-3 py-2 text-sm border border-gray-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs text-gray-500 mb-1.5 font-medium">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="URL, title, or IP address..."
                   class="w-full bg-gray-800 text-white rounded-lg px-3 py-2 text-sm border border-gray-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div class="flex gap-2">
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/>
                </svg>
                Filter
            </button>
            @if(request()->hasAny(['platform', 'status', 'search', 'date_from', 'date_to']))
                <a href="{{ route('admin.logs') }}"
                   class="bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    Clear
                </a>
            @endif
        </div>
    </form>
</div>

{{-- Table --}}
<div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
    {{-- Table Header --}}
    <div class="px-5 py-3 border-b border-gray-800 flex items-center justify-between">
        <p class="text-sm text-gray-400">
            Showing <span class="text-white font-medium">{{ $logs->firstItem() ?? 0 }}</span>–<span class="text-white font-medium">{{ $logs->lastItem() ?? 0 }}</span>
            of <span class="text-white font-medium">{{ number_format($logs->total()) }}</span> entries
        </p>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.export', request()->query()) }}"
               class="bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-800/50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="text-left px-5 py-3 font-medium">ID</th>
                    <th class="text-left px-5 py-3 font-medium">Platform</th>
                    <th class="text-left px-5 py-3 font-medium">URL / Title</th>
                    <th class="text-left px-5 py-3 font-medium">IP Address</th>
                    <th class="text-left px-5 py-3 font-medium">Browser</th>
                    <th class="text-center px-5 py-3 font-medium">Status</th>
                    <th class="text-left px-5 py-3 font-medium">Date</th>
                    <th class="text-center px-5 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($logs as $log)
                    @php
                        $badges = [
                            'tiktok' => 'bg-pink-500/15 text-pink-400 ring-1 ring-pink-500/20',
                            'facebook' => 'bg-blue-500/15 text-blue-400 ring-1 ring-blue-500/20',
                            'pinterest' => 'bg-red-500/15 text-red-400 ring-1 ring-red-500/20',
                            'youtube' => 'bg-red-600/15 text-red-400 ring-1 ring-red-600/20',
                        ];
                        // Parse browser from user agent
                        $browser = 'Unknown';
                        if ($log->user_agent) {
                            if (str_contains($log->user_agent, 'Edg/')) $browser = 'Edge';
                            elseif (str_contains($log->user_agent, 'OPR/')) $browser = 'Opera';
                            elseif (str_contains($log->user_agent, 'Chrome/')) $browser = 'Chrome';
                            elseif (str_contains($log->user_agent, 'Firefox/')) $browser = 'Firefox';
                            elseif (str_contains($log->user_agent, 'Safari/')) $browser = 'Safari';
                        }
                    @endphp
                    <tr class="hover:bg-gray-800/30 transition group">
                        <td class="px-5 py-3 text-gray-600 text-xs font-mono">#{{ $log->id }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $badges[$log->platform] ?? 'bg-gray-700 text-gray-400' }}">
                                {{ ucfirst($log->platform) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 max-w-xs">
                            <a href="{{ $log->source_url }}" target="_blank" rel="noopener noreferrer" class="block hover:opacity-80 transition">
                                @if($log->video_title)
                                    <p class="text-gray-200 text-xs truncate font-medium hover:text-indigo-400 transition" title="{{ $log->video_title }}">{{ $log->video_title }}</p>
                                @endif
                                <p class="text-gray-600 text-[10px] truncate hover:text-indigo-400/60 transition" title="{{ $log->source_url }}">{{ $log->source_url }}</p>
                            </a>
                        </td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-400">{{ $log->ip_address }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400">{{ $browser }}</td>
                        <td class="px-5 py-3 text-center">
                            @if($log->downloaded)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-500/15 text-green-400 rounded-full text-[10px] font-semibold ring-1 ring-green-500/20">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Downloaded
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-700/50 text-gray-500 rounded-full text-[10px] font-medium">
                                    Fetched
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">
                            <p>{{ $log->created_at->format('M d, Y') }}</p>
                            <p class="text-gray-600">{{ $log->created_at->format('h:i:s A') }}</p>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <form method="POST" action="{{ route('admin.logs.delete', $log->id) }}"
                                  onsubmit="return confirm('Delete this log entry?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 text-gray-600 hover:text-red-400 rounded-lg hover:bg-red-500/10 transition opacity-0 group-hover:opacity-100"
                                        title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center">
                            <svg class="w-12 h-12 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                            </svg>
                            <p class="text-gray-600 text-sm">No logs found</p>
                            <p class="text-gray-700 text-xs mt-1">Try adjusting your filters</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
        <div class="px-5 py-4 border-t border-gray-800 flex items-center justify-between">
            <p class="text-xs text-gray-600">Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}</p>
            <div class="flex gap-1">
                @if($logs->onFirstPage())
                    <span class="px-3 py-1.5 bg-gray-800 text-gray-600 rounded-lg text-xs cursor-not-allowed">Previous</span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white rounded-lg text-xs transition">Previous</a>
                @endif
                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white rounded-lg text-xs transition">Next</a>
                @else
                    <span class="px-3 py-1.5 bg-gray-800 text-gray-600 rounded-lg text-xs cursor-not-allowed">Next</span>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
