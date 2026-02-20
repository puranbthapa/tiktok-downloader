


<?php $__env->startSection('title', 'Pinterest Video Downloader - Download Pinterest Videos & Pins'); ?>
<?php $__env->startSection('meta_description', 'Download Pinterest videos and image pins for free. Save Pinterest video pins to your device in high quality. No registration or app installation needed.'); ?>
<?php $__env->startSection('meta_keywords', 'pinterest downloader, pinterest video downloader, download pinterest videos, pinterest pin downloader, save pinterest videos, pinterest image downloader'); ?>

<?php $__env->startSection('structured_data'); ?>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "How to download Pinterest videos?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Copy the Pinterest pin URL, paste it into our downloader, and click Download to save the video or image."
            }
        },
        {
            "@type": "Question",
            "name": "Can I download Pinterest images too?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes, our downloader supports both Pinterest video pins and image pins."
            }
        }
    ]
}
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">Pinterest Downloader</h1>
        <p class="text-gray-400">Download Pinterest videos &amp; pins</p>
    </div>

    
    <form action="<?php echo e(route('pinterest.download')); ?>" method="POST" class="mb-6">
        <?php echo csrf_field(); ?>
        <div class="flex gap-3">
            <input
                type="text"
                name="url"
                placeholder="Paste Pinterest pin URL here..."
                value="<?php echo e(old('url')); ?>"
                class="flex-1 bg-gray-800 text-white rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-red-500 border border-gray-700"
            />
            <button type="submit"
                class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-xl transition">
                Download
            </button>
        </div>
        <?php $__errorArgs = ['url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="text-red-400 mt-2 text-sm"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </form>

    
    <?php if(session('error')): ?>
        <div class="bg-red-500/20 border border-red-500 text-red-300 rounded-xl p-4 mb-6">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    
    <?php if(isset($video)): ?>
    <div class="bg-gray-800 rounded-2xl overflow-hidden border border-gray-700">
        <?php if($video['cover']): ?>
            <img src="<?php echo e($video['cover']); ?>" class="w-full h-52 object-cover" alt="thumbnail">
        <?php endif; ?>
        <div class="p-5">
            <p class="text-white font-semibold text-lg mb-1"><?php echo e($video['title']); ?></p>
            <?php if($video['description']): ?>
                <p class="text-gray-400 text-sm mb-4"><?php echo e(Str::limit($video['description'], 120)); ?></p>
            <?php endif; ?>

            <div class="flex flex-col gap-3">
                <?php if($video['video']): ?>
                <a href="<?php echo e(route('pinterest.stream', ['url' => $video['video']])); ?>"
                   class="bg-red-600 hover:bg-red-700 text-white text-center font-semibold py-3 rounded-xl transition">
                    ⬇ Download Video
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="mt-10 text-left">
        <h2 class="text-xl font-bold text-white mb-4">Frequently Asked Questions</h2>
        <div class="space-y-4">
            <div>
                <h3 class="text-white font-semibold text-sm">How to download Pinterest videos?</h3>
                <p class="text-gray-400 text-sm mt-1">Open the Pinterest pin, copy the URL from your browser, paste it in the input field above, and click Download.</p>
            </div>
            <div>
                <h3 class="text-white font-semibold text-sm">Can I download Pinterest images too?</h3>
                <p class="text-gray-400 text-sm mt-1">Yes! Our downloader supports both video pins and image pins from Pinterest.</p>
            </div>
            <div>
                <h3 class="text-white font-semibold text-sm">Is it free to use?</h3>
                <p class="text-gray-400 text-sm mt-1">Absolutely! Our Pinterest downloader is 100% free with no registration, no app installation, and no download limits.</p>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\tiktok-downloader\resources\views/pinterest/index.blade.php ENDPATH**/ ?>