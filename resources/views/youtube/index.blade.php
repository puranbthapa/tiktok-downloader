{{-- resources/views/youtube/index.blade.php --}}
@extends('layouts.app')

@section('title', 'YouTube Video Downloader - Download YouTube Videos in HD, 4K')
@section('meta_description', 'Download YouTube videos and shorts in HD, Full HD, and 4K quality. Free YouTube video downloader with multiple format options. No registration required.')
@section('meta_keywords', 'youtube downloader, youtube video downloader, download youtube videos, youtube to mp4, youtube downloader hd, youtube shorts downloader, save youtube videos')

@section('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "FAQPage",
    "mainEntity": [
        {
            "@@type": "Question",
            "name": "How to download YouTube videos?",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "Copy the YouTube video URL, paste it into our downloader, click Download, and choose your preferred quality (360p, 720p, 1080p, or 4K)."
            }
        },
        {
            "@@type": "Question",
            "name": "Can I download YouTube Shorts?",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "Yes, our downloader supports YouTube Shorts. Just paste the Shorts URL and download it like any other video."
            }
        }
    ]
}
</script>
@endsection

@section('content')
<div class="w-full">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">YouTube Downloader</h1>
        <p class="text-gray-400">Download YouTube videos &amp; shorts in multiple qualities</p>
    </div>

    {{-- Form --}}
    <form action="{{ route('youtube.download') }}" method="POST" class="mb-6">
        @csrf
        <div class="flex gap-3">
            <input
                type="text"
                name="url"
                placeholder="Paste YouTube URL here..."
                value="{{ old('url') }}"
                class="flex-1 bg-gray-800 text-white rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-red-500 border border-gray-700"
            />
            <button type="submit"
                class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-xl transition">
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
            <p class="text-gray-400 text-sm mb-4">By: {{ $video['author'] }} · {{ $video['duration'] }}</p>

            <div class="flex flex-col gap-3">
                {{-- Combined formats (audio + video) --}}
                @foreach($video['combined'] as $fmt)
                <a href="{{ route('youtube.stream', ['url' => $fmt['url']]) }}"
                   class="{{ $loop->first ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-700 hover:bg-gray-600' }} text-white text-center font-semibold py-3 rounded-xl transition flex items-center justify-center gap-2">
                    <span>⬇ Download {{ $fmt['quality'] }}</span>
                    @if($fmt['size'])
                        <span class="text-xs opacity-70">({{ $fmt['size'] }})</span>
                    @endif
                </a>
                @endforeach

                {{-- Video-only formats (higher quality) --}}
                @if(!empty($video['videoOnly']))
                    <div class="border-t border-gray-700 pt-3 mt-1">
                        <p class="text-gray-500 text-xs text-center mb-2">Higher quality (video only — no audio)</p>
                    </div>
                    @foreach($video['videoOnly'] as $fmt)
                    <a href="{{ route('youtube.stream', ['url' => $fmt['url']]) }}"
                       class="bg-gray-700 hover:bg-gray-600 text-white text-center font-semibold py-3 rounded-xl transition flex items-center justify-center gap-2">
                        <span>⬇ {{ $fmt['quality'] }}</span>
                        @if($fmt['size'])
                            <span class="text-xs opacity-70">({{ $fmt['size'] }})</span>
                        @endif
                    </a>
                    @endforeach
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
                <h3 class="text-white font-semibold text-sm">How to download YouTube videos?</h3>
                <p class="text-gray-400 text-sm mt-1">Copy the YouTube video URL from your browser address bar, paste it in the input field above, click Download, and choose your preferred quality.</p>
            </div>
            <div>
                <h3 class="text-white font-semibold text-sm">Can I download YouTube Shorts?</h3>
                <p class="text-gray-400 text-sm mt-1">Yes! Our downloader fully supports YouTube Shorts. Just paste the Shorts URL and download it like any regular video.</p>
            </div>
            <div>
                <h3 class="text-white font-semibold text-sm">What quality options are available?</h3>
                <p class="text-gray-400 text-sm mt-1">We offer multiple quality options including 360p, 480p, 720p (HD), 1080p (Full HD), and higher when available. Choose the quality that suits your needs.</p>
            </div>
        </div>
    </div>
</div>
@endsection
