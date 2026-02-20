{{-- resources/views/pinterest/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Pinterest Video Downloader - Download Pinterest Videos & Pins')
@section('meta_description', 'Download Pinterest videos and image pins for free. Save Pinterest video pins to your device in high quality. No registration or app installation needed.')
@section('meta_keywords', 'pinterest downloader, pinterest video downloader, download pinterest videos, pinterest pin downloader, save pinterest videos, pinterest image downloader')

@section('structured_data')
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
@endsection

@section('content')
<div class="w-full">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">Pinterest Downloader</h1>
        <p class="text-gray-400">Download Pinterest videos &amp; pins</p>
    </div>

    {{-- Form --}}
    <form action="{{ route('pinterest.download') }}" method="POST" class="mb-6">
        @csrf
        <div class="flex gap-3">
            <input
                type="text"
                name="url"
                placeholder="Paste Pinterest pin URL here..."
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
            @if($video['description'])
                <p class="text-gray-400 text-sm mb-4">{{ Str::limit($video['description'], 120) }}</p>
            @endif

            <div class="flex flex-col gap-3">
                @if($video['video'])
                <a href="{{ route('pinterest.stream', ['url' => $video['video']]) }}"
                   class="bg-red-600 hover:bg-red-700 text-white text-center font-semibold py-3 rounded-xl transition">
                    ⬇ Download Video
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
@endsection
