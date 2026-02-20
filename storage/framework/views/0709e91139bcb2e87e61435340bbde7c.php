<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Admin Panel'); ?> — Video Downloader</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%230f172a'/><path d='M30 65 L50 35 L70 65' stroke='%23ec4899' stroke-width='8' fill='none' stroke-linecap='round' stroke-linejoin='round'/><circle cx='50' cy='28' r='5' fill='%2360a5fa'/></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sidebar: { DEFAULT: '#0f172a', hover: '#1e293b' },
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .scrollbar-thin::-webkit-scrollbar { width: 4px; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #334155; border-radius: 2px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-950 text-gray-200 min-h-screen flex">

    
    <aside class="w-64 bg-sidebar min-h-screen border-r border-gray-800 flex flex-col fixed left-0 top-0 bottom-0 z-30">
        
        <div class="p-5 border-b border-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-indigo-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-white font-bold text-sm">Admin Panel</p>
                    <p class="text-gray-500 text-xs">Video Downloader</p>
                </div>
            </div>
        </div>

        
        <nav class="flex-1 p-3 space-y-1 overflow-y-auto scrollbar-thin">
            <a href="<?php echo e(route('admin.dashboard')); ?>"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      <?php echo e(request()->routeIs('admin.dashboard') ? 'bg-indigo-600/20 text-indigo-400' : 'text-gray-400 hover:bg-sidebar-hover hover:text-white'); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                </svg>
                Dashboard
            </a>
            <a href="<?php echo e(route('admin.logs')); ?>"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      <?php echo e(request()->routeIs('admin.logs') ? 'bg-indigo-600/20 text-indigo-400' : 'text-gray-400 hover:bg-sidebar-hover hover:text-white'); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 0v1.5c0 .621-.504 1.125-1.125 1.125M12 16.875c0 .621.504 1.125 1.125 1.125"/>
                </svg>
                Download Logs
            </a>
            <a href="<?php echo e(route('admin.settings')); ?>"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      <?php echo e(request()->routeIs('admin.settings') ? 'bg-indigo-600/20 text-indigo-400' : 'text-gray-400 hover:bg-sidebar-hover hover:text-white'); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Settings
            </a>

            <div class="pt-4 mt-4 border-t border-gray-800">
                <p class="px-3 text-xs text-gray-600 uppercase tracking-wider mb-2">Quick Actions</p>
                <a href="<?php echo e(route('admin.export')); ?>" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:bg-sidebar-hover hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Export CSV
                </a>
                <a href="<?php echo e(route('home')); ?>" target="_blank"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:bg-sidebar-hover hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                    </svg>
                    View Site
                </a>
            </div>
        </nav>

        
        <div class="p-4 border-t border-gray-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                        <span class="text-white text-xs font-bold">A</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white">Admin</p>
                        <p class="text-xs text-gray-500">Administrator</p>
                    </div>
                </div>
                <a href="<?php echo e(route('admin.logout')); ?>" title="Logout"
                   class="p-1.5 text-gray-500 hover:text-red-400 rounded-lg hover:bg-gray-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                    </svg>
                </a>
            </div>
        </div>
    </aside>

    
    <main class="flex-1 ml-64 min-h-screen">
        
        <header class="sticky top-0 z-20 bg-gray-950/80 backdrop-blur-lg border-b border-gray-800 px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-bold text-white"><?php echo $__env->yieldContent('page-title', 'Dashboard'); ?></h1>
                    <p class="text-xs text-gray-500 mt-0.5"><?php echo $__env->yieldContent('page-subtitle', 'Overview of your video downloader'); ?></p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-xs text-gray-500"><?php echo e(now()->format('M d, Y — h:i A')); ?></span>
                </div>
            </div>
        </header>

        
        <?php if(session('success')): ?>
            <div class="mx-8 mt-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="bg-green-500/10 border border-green-500/30 text-green-400 text-sm rounded-lg px-4 py-3 flex items-center justify-between">
                    <span><?php echo e(session('success')); ?></span>
                    <button @click="show = false" class="text-green-400 hover:text-green-300">&times;</button>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="p-8">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\tiktok-downloader\resources\views/admin/layout.blade.php ENDPATH**/ ?>