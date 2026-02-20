{{-- resources/views/facebook/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Facebook Video Downloader - Download Facebook Reels & Videos in HD')
@section('meta_description', 'Download Facebook videos and reels in HD and SD quality. Free Facebook video downloader — save public Facebook videos to your device. No login required.')
@section('meta_keywords', 'facebook video downloader, download facebook videos, facebook reels downloader, fb video downloader, save facebook videos, facebook downloader hd')

@section('structured_data')
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
@endsection

@section('content')
<div class="w-full">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">Facebook Downloader</h1>
        <p class="text-gray-400">Download Facebook reels &amp; videos in HD</p>
    </div>

    {{-- Form --}}
    <form action="{{ route('facebook.download') }}" method="POST" class="mb-6">
        @csrf
        <div class="flex gap-3">
            <input
                type="text"
                name="url"
                placeholder="Paste Facebook video URL here..."
                value="{{ old('url') }}"
                class="flex-1 bg-gray-800 text-white rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500 border border-gray-700"
            />
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition">
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
            @if($video['duration'])
                <p class="text-gray-400 text-sm mb-4">Duration: {{ $video['duration'] }}</p>
            @endif

            <div class="flex flex-col gap-3">
                @if($video['hd'])
                <a href="{{ route('facebook.stream', ['url' => $video['hd']]) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white text-center font-semibold py-3 rounded-xl transition">
                    ⬇ Download HD
                </a>
                @endif
                @if($video['sd'])
                <a href="{{ route('facebook.stream', ['url' => $video['sd']]) }}"
                   class="bg-gray-700 hover:bg-gray-600 text-white text-center font-semibold py-3 rounded-xl transition">
                    ⬇ Download SD
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
@endsection
