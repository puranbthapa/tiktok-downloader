

<?php $__env->startSection('page-title', 'Settings'); ?>
<?php $__env->startSection('page-subtitle', 'Admin panel configuration'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-2xl">
    
    <div class="bg-gray-900 rounded-xl p-6 border border-gray-800 mb-6">
        <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
            </svg>
            System Information
        </h2>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div class="bg-gray-800/50 rounded-lg px-4 py-3">
                <p class="text-gray-500 text-xs mb-1">Laravel Version</p>
                <p class="text-white font-medium"><?php echo e(app()->version()); ?></p>
            </div>
            <div class="bg-gray-800/50 rounded-lg px-4 py-3">
                <p class="text-gray-500 text-xs mb-1">PHP Version</p>
                <p class="text-white font-medium"><?php echo e(phpversion()); ?></p>
            </div>
            <div class="bg-gray-800/50 rounded-lg px-4 py-3">
                <p class="text-gray-500 text-xs mb-1">Environment</p>
                <p class="text-white font-medium capitalize"><?php echo e(app()->environment()); ?></p>
            </div>
            <div class="bg-gray-800/50 rounded-lg px-4 py-3">
                <p class="text-gray-500 text-xs mb-1">Debug Mode</p>
                <p class="font-medium <?php echo e(config('app.debug') ? 'text-yellow-400' : 'text-green-400'); ?>">
                    <?php echo e(config('app.debug') ? 'Enabled' : 'Disabled'); ?>

                </p>
            </div>
            <div class="bg-gray-800/50 rounded-lg px-4 py-3">
                <p class="text-gray-500 text-xs mb-1">Session Driver</p>
                <p class="text-white font-medium capitalize"><?php echo e(config('session.driver')); ?></p>
            </div>
            <div class="bg-gray-800/50 rounded-lg px-4 py-3">
                <p class="text-gray-500 text-xs mb-1">Database</p>
                <p class="text-white font-medium"><?php echo e(config('database.connections.' . config('database.default') . '.database')); ?></p>
            </div>
        </div>
    </div>

    
    <div class="bg-gray-900 rounded-xl p-6 border border-red-900/30 mb-6">
        <h2 class="font-semibold text-red-400 mb-2 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
            Danger Zone
        </h2>
        <p class="text-gray-500 text-sm mb-4">Permanently delete all download log records. This action cannot be undone.</p>

        <div x-data="{ showConfirm: false }">
            <button @click="showConfirm = true" x-show="!showConfirm"
                    class="bg-red-600/20 hover:bg-red-600/30 text-red-400 border border-red-600/30 px-4 py-2 rounded-lg text-sm font-medium transition">
                Clear All Logs
            </button>

            <div x-show="showConfirm" x-cloak class="mt-3 bg-red-950/30 border border-red-900/30 rounded-lg p-4">
                <p class="text-red-300 text-sm mb-3">Type <code class="bg-red-900/30 px-1.5 py-0.5 rounded text-red-400 font-mono text-xs">DELETE</code> to confirm:</p>
                <form method="POST" action="<?php echo e(route('admin.logs.clear')); ?>" class="flex gap-2">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <input type="text" name="confirm" placeholder="Type DELETE" required
                           class="bg-gray-800 text-white rounded-lg px-3 py-2 text-sm border border-red-800/50 focus:ring-2 focus:ring-red-500 focus:outline-none flex-1"
                           autocomplete="off">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        Confirm Delete
                    </button>
                    <button type="button" @click="showConfirm = false" class="bg-gray-800 hover:bg-gray-700 text-gray-400 px-4 py-2 rounded-lg text-sm transition">
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    </div>

    
    <div class="bg-gray-900 rounded-xl p-6 border border-gray-800">
        <h2 class="font-semibold text-white mb-2 flex items-center gap-2">
            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>
            </svg>
            Credentials
        </h2>
        <p class="text-gray-500 text-sm mb-3">Admin login credentials are configured via environment variables in your <code class="bg-gray-800 px-1.5 py-0.5 rounded text-gray-300 text-xs">.env</code> file:</p>
        <div class="bg-gray-800/80 rounded-lg p-4 font-mono text-sm">
            <p class="text-gray-400"><span class="text-indigo-400">ADMIN_USERNAME</span>=<span class="text-green-400">your_username</span></p>
            <p class="text-gray-400"><span class="text-indigo-400">ADMIN_PASSWORD</span>=<span class="text-green-400">your_password</span></p>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\tiktok-downloader\resources\views/admin/settings.blade.php ENDPATH**/ ?>