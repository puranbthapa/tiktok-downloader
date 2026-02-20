

<?php $__env->startSection('page-title', 'Dashboard'); ?>
<?php $__env->startSection('page-subtitle', 'Real-time overview of downloads and visitors'); ?>

<?php $__env->startSection('content'); ?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
    <div class="bg-gray-900 rounded-xl p-5 border border-gray-800 hover:border-gray-700 transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Fetches</span>
            <div class="w-8 h-8 bg-blue-500/10 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-white"><?php echo e(number_format($totalFetches)); ?></p>
        <p class="text-xs text-gray-600 mt-1">All time</p>
    </div>

    <div class="bg-gray-900 rounded-xl p-5 border border-gray-800 hover:border-gray-700 transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Downloads</span>
            <div class="w-8 h-8 bg-green-500/10 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-green-400"><?php echo e(number_format($totalDownloads)); ?></p>
        <p class="text-xs text-gray-600 mt-1">Actual streams</p>
    </div>

    <div class="bg-gray-900 rounded-xl p-5 border border-gray-800 hover:border-gray-700 transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Conversion</span>
            <div class="w-8 h-8 bg-yellow-500/10 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-yellow-400"><?php echo e($conversionRate); ?>%</p>
        <p class="text-xs text-gray-600 mt-1">Fetch → Download</p>
    </div>

    <div class="bg-gray-900 rounded-xl p-5 border border-gray-800 hover:border-gray-700 transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Today</span>
            <div class="w-8 h-8 bg-indigo-500/10 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-indigo-400"><?php echo e(number_format($todayFetches)); ?></p>
        <p class="text-xs text-gray-600 mt-1"><?php echo e($todayDownloads); ?> downloaded</p>
    </div>

    <div class="bg-gray-900 rounded-xl p-5 border border-gray-800 hover:border-gray-700 transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Visitors</span>
            <div class="w-8 h-8 bg-purple-500/10 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-purple-400"><?php echo e(number_format($uniqueVisitors)); ?></p>
        <p class="text-xs text-gray-600 mt-1">Unique IPs</p>
    </div>

    <div class="bg-gray-900 rounded-xl p-5 border border-gray-800 hover:border-gray-700 transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Platforms</span>
            <div class="w-8 h-8 bg-pink-500/10 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 012.25-2.25h7.5A2.25 2.25 0 0118 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 004.5 9v.878m13.5-3A2.25 2.25 0 0119.5 9v.878m0 0a2.246 2.246 0 00-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0121 12v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6c0-1.243 1.007-2.25 2.25-2.25z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-pink-400"><?php echo e($platformStats->count()); ?></p>
        <p class="text-xs text-gray-600 mt-1">Active platforms</p>
    </div>
</div>


<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
    
    <div class="xl:col-span-2 bg-gray-900 rounded-xl p-6 border border-gray-800">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-semibold text-white">Activity — Last 14 Days</h2>
            <div class="flex items-center gap-4 text-xs">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-indigo-500 rounded-full inline-block"></span> Fetches</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-green-500 rounded-full inline-block"></span> Downloads</span>
            </div>
        </div>
        <?php
            $maxVal = max($dailyStats->max('fetches') ?: 1, 1);
            $days = collect();
            for ($i = 13; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $stat = $dailyStats->firstWhere('date', $date);
                $days->push((object)[
                    'date' => $date,
                    'fetches' => $stat->fetches ?? 0,
                    'downloads' => $stat->downloads ?? 0,
                ]);
            }
        ?>
        <div class="flex items-end gap-1.5 h-48">
            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex-1 flex flex-col items-center gap-1 group relative">
                    <div class="absolute bottom-full mb-2 hidden group-hover:flex flex-col items-center z-10">
                        <div class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-xs whitespace-nowrap shadow-xl">
                            <p class="text-white font-medium"><?php echo e(\Carbon\Carbon::parse($day->date)->format('M d')); ?></p>
                            <p class="text-indigo-400"><?php echo e($day->fetches); ?> fetches</p>
                            <p class="text-green-400"><?php echo e($day->downloads); ?> downloads</p>
                        </div>
                    </div>
                    <div class="w-full flex flex-col items-center gap-0.5" style="height: 180px;">
                        <div class="w-full flex items-end gap-0.5 h-full">
                            <div class="flex-1 bg-indigo-500/80 rounded-t-sm transition-all duration-300 hover:bg-indigo-400"
                                 style="height: <?php echo e($maxVal > 0 ? round(($day->fetches / $maxVal) * 100) : 0); ?>%; min-height: <?php echo e($day->fetches > 0 ? '4px' : '0'); ?>"></div>
                            <div class="flex-1 bg-green-500/80 rounded-t-sm transition-all duration-300 hover:bg-green-400"
                                 style="height: <?php echo e($maxVal > 0 ? round(($day->downloads / $maxVal) * 100) : 0); ?>%; min-height: <?php echo e($day->downloads > 0 ? '4px' : '0'); ?>"></div>
                        </div>
                    </div>
                    <span class="text-[10px] text-gray-600 mt-1"><?php echo e(\Carbon\Carbon::parse($day->date)->format('d')); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div class="bg-gray-900 rounded-xl p-6 border border-gray-800">
        <h2 class="font-semibold text-white mb-6">Platform Breakdown</h2>
        <?php
            $platformColors = [
                'tiktok'    => ['bg' => 'bg-pink-500', 'text' => 'text-pink-400'],
                'facebook'  => ['bg' => 'bg-blue-500', 'text' => 'text-blue-400'],
                'pinterest' => ['bg' => 'bg-red-500',  'text' => 'text-red-400'],
                'youtube'   => ['bg' => 'bg-red-600',  'text' => 'text-red-400'],
            ];
        ?>
        <?php $__empty_1 = true; $__currentLoopData = $platformStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $c = $platformColors[$stat->platform] ?? ['bg' => 'bg-gray-500', 'text' => 'text-gray-400'];
                $pct = $totalFetches > 0 ? round(($stat->total / $totalFetches) * 100) : 0;
                $dlPct = $stat->total > 0 ? round(($stat->downloads / $stat->total) * 100) : 0;
            ?>
            <div class="mb-5 last:mb-0">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 <?php echo e($c['bg']); ?> rounded-full"></div>
                        <span class="text-sm font-medium text-white capitalize"><?php echo e($stat->platform); ?></span>
                    </div>
                    <span class="text-xs text-gray-500"><?php echo e($stat->total); ?> fetches &bull; <?php echo e($stat->downloads); ?> DL</span>
                </div>
                <div class="w-full bg-gray-800 rounded-full h-2.5">
                    <div class="<?php echo e($c['bg']); ?> h-2.5 rounded-full transition-all duration-700" style="width: <?php echo e($pct); ?>%"></div>
                </div>
                <div class="flex justify-between mt-1">
                    <span class="text-[10px] text-gray-600"><?php echo e($pct); ?>% of total</span>
                    <span class="text-[10px] text-gray-600"><?php echo e($dlPct); ?>% conversion</span>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-8">
                <p class="text-gray-600 text-sm">No platform data yet</p>
            </div>
        <?php endif; ?>
    </div>
</div>


<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
    
    <div class="bg-gray-900 rounded-xl p-6 border border-gray-800">
        <h2 class="font-semibold text-white mb-4">Today's Hourly Activity</h2>
        <?php $maxHour = $hourlyStats->max('total') ?: 1; ?>
        <div class="flex items-end gap-0.5 h-32">
            <?php for($h = 0; $h < 24; $h++): ?>
                <?php $val = $hourlyStats[$h]->total ?? 0; ?>
                <div class="flex-1 group relative">
                    <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 hidden group-hover:block z-10">
                        <div class="bg-gray-800 border border-gray-700 rounded px-2 py-1 text-[10px] text-white whitespace-nowrap shadow-lg">
                            <?php echo e(str_pad($h, 2, '0', STR_PAD_LEFT)); ?>:00 — <?php echo e($val); ?>

                        </div>
                    </div>
                    <div class="w-full bg-indigo-500/60 rounded-t-sm hover:bg-indigo-400 transition-all cursor-pointer"
                         style="height: <?php echo e($maxHour > 0 ? max(round(($val / $maxHour) * 128), ($val > 0 ? 4 : 1)) : 1); ?>px">
                    </div>
                </div>
            <?php endfor; ?>
        </div>
        <div class="flex justify-between mt-2 text-[10px] text-gray-600">
            <span>00:00</span><span>06:00</span><span>12:00</span><span>18:00</span><span>23:00</span>
        </div>
    </div>

    
    <div class="xl:col-span-2 bg-gray-900 rounded-xl p-6 border border-gray-800">
        <h2 class="font-semibold text-white mb-4">Top 10 URLs</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-500 text-xs uppercase">
                        <th class="text-left pb-3 font-medium">#</th>
                        <th class="text-left pb-3 font-medium">Platform</th>
                        <th class="text-left pb-3 font-medium">URL / Title</th>
                        <th class="text-right pb-3 font-medium">Hits</th>
                        <th class="text-right pb-3 font-medium">DL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php $__empty_1 = true; $__currentLoopData = $topUrls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $badges = [
                                'tiktok' => 'bg-pink-500/15 text-pink-400 ring-1 ring-pink-500/20',
                                'facebook' => 'bg-blue-500/15 text-blue-400 ring-1 ring-blue-500/20',
                                'pinterest' => 'bg-red-500/15 text-red-400 ring-1 ring-red-500/20',
                                'youtube' => 'bg-red-600/15 text-red-400 ring-1 ring-red-600/20',
                            ];
                        ?>
                        <tr class="hover:bg-gray-800/50 transition">
                            <td class="py-2.5 text-gray-600 text-xs"><?php echo e($i + 1); ?></td>
                            <td class="py-2.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium <?php echo e($badges[$url->platform] ?? 'bg-gray-700 text-gray-400'); ?>">
                                    <?php echo e(ucfirst($url->platform)); ?>

                                </span>
                            </td>
                            <td class="py-2.5 max-w-xs">
                                <a href="<?php echo e($url->source_url); ?>" target="_blank" rel="noopener noreferrer" class="block hover:opacity-80 transition">
                                    <?php if($url->video_title): ?>
                                        <p class="text-gray-300 text-xs truncate hover:text-indigo-400 transition" title="<?php echo e($url->video_title); ?>"><?php echo e($url->video_title); ?></p>
                                    <?php endif; ?>
                                    <p class="text-gray-600 text-[10px] truncate hover:text-indigo-400/60 transition" title="<?php echo e($url->source_url); ?>"><?php echo e($url->source_url); ?></p>
                                </a>
                            </td>
                            <td class="py-2.5 text-right text-white font-medium text-xs"><?php echo e($url->hits); ?></td>
                            <td class="py-2.5 text-right text-green-400 text-xs"><?php echo e($url->dl_count); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-600 text-sm">No data yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    
    <div class="bg-gray-900 rounded-xl p-6 border border-gray-800">
        <h2 class="font-semibold text-white mb-4">Browser Usage</h2>
        <?php
            $browserColors = ['Chrome' => 'bg-green-500', 'Firefox' => 'bg-orange-500', 'Safari' => 'bg-blue-400', 'Edge' => 'bg-cyan-500', 'Opera' => 'bg-red-500', 'Other' => 'bg-gray-500'];
            $browserTotal = $browsers->sum('total') ?: 1;
        ?>
        <div class="space-y-3">
            <?php $__empty_1 = true; $__currentLoopData = $browsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $browser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $bpct = round(($browser->total / $browserTotal) * 100); ?>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-300"><?php echo e($browser->name); ?></span>
                        <span class="text-gray-500 text-xs"><?php echo e($browser->total); ?> (<?php echo e($bpct); ?>%)</span>
                    </div>
                    <div class="w-full bg-gray-800 rounded-full h-2">
                        <div class="<?php echo e($browserColors[$browser->name] ?? 'bg-gray-500'); ?> h-2 rounded-full transition-all duration-500"
                             style="width: <?php echo e($bpct); ?>%"></div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-gray-600 text-sm text-center py-4">No browser data yet</p>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="bg-gray-900 rounded-xl p-6 border border-gray-800">
        <h2 class="font-semibold text-white mb-4">Recent Visitors</h2>
        <div class="overflow-x-auto max-h-64 overflow-y-auto scrollbar-thin">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-gray-900">
                    <tr class="text-gray-500 text-xs uppercase">
                        <th class="text-left pb-2 font-medium">IP Address</th>
                        <th class="text-right pb-2 font-medium">Requests</th>
                        <th class="text-right pb-2 font-medium">Last Seen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php $__empty_1 = true; $__currentLoopData = $recentVisitors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-800/50 transition">
                            <td class="py-2 font-mono text-xs text-gray-300"><?php echo e($v->ip_address); ?></td>
                            <td class="py-2 text-right">
                                <span class="px-2 py-0.5 bg-indigo-500/15 text-indigo-400 rounded-full text-xs font-medium"><?php echo e($v->requests); ?></span>
                            </td>
                            <td class="py-2 text-right text-gray-500 text-xs"><?php echo e(\Carbon\Carbon::parse($v->last_seen)->diffForHumans()); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3" class="py-4 text-center text-gray-600 text-sm">No visitors yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\tiktok-downloader\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>