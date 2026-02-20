{{-- resources/views/tiktok/index.blade.php --}}
@extends('layouts.app')

@section('title', 'TikTok Video Downloader - Download TikTok Videos Without Watermark')
@section('meta_description', 'Download TikTok videos without watermark in HD quality. Free TikTok video downloader — save TikTok videos, reels, and stories to your device. No registration needed.')
@section('meta_keywords', 'tiktok downloader, tiktok video downloader, download tiktok without watermark, tiktok saver, save tiktok videos, tiktok downloader hd, tiktok no watermark')

@section('structured_data')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "How to download TikTok videos without watermark?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Copy the TikTok video URL, paste it into our downloader, and click Download. The video will be saved without watermark in HD quality."
            }
        },
        {
            "@type": "Question",
            "name": "Is this TikTok downloader free?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes, our TikTok video downloader is 100% free. No registration, no software installation required."
            }
        }
    ]
}
</script>
@endsection

@section('content')
<div class="w-full">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">TikTok Downloader</h1>
        <p class="text-gray-400">Download TikTok videos without watermark</p>
    </div>

    {{-- Form --}}
    <form action="{{ route('tiktok.download') }}" method="POST" class="mb-6">
        @csrf
        <div class="flex gap-3">
            <input
                type="text"
                name="url"
                placeholder="Paste TikTok URL here..."
                value="{{ old('url') }}"
                class="flex-1 bg-gray-800 text-white rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-pink-500 border border-gray-700"
            />
            <button type="submit"
                class="bg-pink-600 hover:bg-pink-700 text-white font-semibold px-6 py-3 rounded-xl transition">
                Download
            </button>
        </div>
        @error('url')
            <p class="text-red-400 mt-2 text-sm">{{ $message }}</p>
        @enderror
    </form>

    {{-- Error --}}
    @if(session('error'))
        <div class="bg-red-500/20 border border-red-500 text-red-300 rounded-xl p-4 mb-6">
            {{ session('error') }}
        </div>
    @endif

    {{-- Result --}}
    @if(isset($video))
    <div class="bg-gray-800 rounded-2xl overflow-hidden border border-gray-700">
        @if($video['cover'])
            <img src="{{ $video['cover'] }}" class="w-full h-52 object-cover" alt="thumbnail">
        @endif
        <div class="p-5">
            <p class="text-white font-semibold text-lg mb-1">{{ $video['title'] }}</p>
            <p class="text-gray-400 text-sm mb-4">By: {{ $video['author'] }} · {{ $video['duration'] }}s</p>

            <div class="flex flex-col gap-3">
                @if($video['hdplay'])
                <a href="{{ route('tiktok.stream', ['url' => $video['hdplay']]) }}"
                   class="bg-pink-600 hover:bg-pink-700 text-white text-center font-semibold py-3 rounded-xl transition">
                    ⬇ Download HD (No Watermark)
                </a>
                @endif
                @if($video['play'])
                <a href="{{ route('tiktok.stream', ['url' => $video['play']]) }}"
                   class="bg-gray-700 hover:bg-gray-600 text-white text-center font-semibold py-3 rounded-xl transition">
                    ⬇ Download SD (No Watermark)
                </a>
                @endif
            </div>
        </div>
    </div>
    @endif
    {{-- FAQ Section --}}
    <div class="mt-10 text-left">
        <h2 class="text-xl font-bold text-white mb-4">Frequently Asked Questions</h2>
        <div class="space-y-4">
            <div>
                <h3 class="text-white font-semibold text-sm">How to download TikTok videos without watermark?</h3>
                <p class="text-gray-400 text-sm mt-1">Copy the TikTok video URL from the app or browser, paste it in the input field above, and click Download. You'll get the video without watermark in HD quality.</p>
            </div>
            <div>
                <h3 class="text-white font-semibold text-sm">Is this TikTok downloader free?</h3>
                <p class="text-gray-400 text-sm mt-1">Yes, our TikTok video downloader is completely free. No registration, no app installation, and no limits on downloads.</p>
            </div>
            <div>
                <h3 class="text-white font-semibold text-sm">Can I download TikTok videos on mobile?</h3>
                <p class="text-gray-400 text-sm mt-1">Yes! Our downloader works on all devices — iPhone, Android, PC, and Mac. Just open this page in your mobile browser and paste the link.</p>
            </div>
        </div>
    </div>
</div>
@endsection
