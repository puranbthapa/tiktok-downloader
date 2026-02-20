


<?php $__env->startSection('title', 'Facebook Video Downloader - Download Facebook Reels & Videos in HD'); ?>
<?php $__env->startSection('meta_description', 'Download Facebook videos and reels in HD and SD quality. Free Facebook video downloader — save public Facebook videos to your device. No login required.'); ?>
<?php $__env->startSection('meta_keywords', 'facebook video downloader, download facebook videos, facebook reels downloader, fb video downloader, save facebook videos, facebook downloader hd'); ?>

<?php $__env->startSection('structured_data'); ?>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "How to download Facebook videos?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Copy the Facebook video URL, paste it into our downloader, and click Download. Choose between HD and SD quality."
            }
        },
        {
            "@type": "Question",
            "name": "Can I download Facebook Reels?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes, our downloader supports Facebook Reels, video posts, and stories as long as they are publicly accessible."
            }
        }
    ]
}
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">Facebook Downloader</h1>
        <p class="text-gray-400">Download Facebook reels &amp; videos in HD</p>
    </div>

    
    <form action="<?php echo e(route('facebook.download')); ?>" method="POST" class="mb-6">
        <?php echo csrf_field(); ?>
        <div class="flex gap-3">
            <input
                type="text"
                name="url"
                placeholder="Paste Facebook video URL here..."
                value="<?php echo e(old('url')); ?>"
                class="flex-1 bg-gray-800 text-white rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500 border border-gray-700"
            />
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition">
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
            <?php if($video['duration']): ?>
                <p class="text-gray-400 text-sm mb-4">Duration: <?php echo e($video['duration']); ?></p>
            <?php endif; ?>

            <div class="flex flex-col gap-3">
                <?php if($video['hd']): ?>
                <a href="<?php echo e(route('facebook.stream', ['url' => $video['hd']])); ?>"
                   class="bg-blue-600 hover:bg-blue-700 text-white text-center font-semibold py-3 rounded-xl transition">
                    ⬇ Download HD
                </a>
                <?php endif; ?>
                <?php if($video['sd']): ?>
                <a href="<?php echo e(route('facebook.stream', ['url' => $video['sd']])); ?>"
                   class="bg-gray-700 hover:bg-gray-600 text-white text-center font-semibold py-3 rounded-xl transition">
                    ⬇ Download SD
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
                <h3 class="text-white font-semibold text-sm">How to download Facebook videos?</h3>
                <p class="text-gray-400 text-sm mt-1">Copy the Facebook video URL from your browser or the Facebook app (Share > Copy Link), paste it above, and click Download.</p>
            </div>
            <div>
                <h3 class="text-white font-semibold text-sm">Can I download Facebook Reels?</h3>
                <p class="text-gray-400 text-sm mt-1">Yes! Our downloader supports Facebook Reels, video posts, and stories. Just make sure the video is set to public.</p>
            </div>
            <div>
                <h3 class="text-white font-semibold text-sm">Why can't I download a video?</h3>
                <p class="text-gray-400 text-sm mt-1">Make sure the video is publicly visible. Private videos or videos from closed groups cannot be downloaded.</p>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\tiktok-downloader\resources\views/facebook/index.blade.php ENDPATH**/ ?>