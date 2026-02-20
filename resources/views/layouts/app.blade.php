{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Free Video Downloader - TikTok, YouTube, Facebook, Pinterest')</title>
    <meta name="description" content="@yield('meta_description', 'Free online video downloader. Download videos from TikTok without watermark, YouTube in HD, Facebook reels, and Pinterest pins. Fast, free, no registration required.')">
    <meta name="keywords" content="@yield('meta_keywords', 'video downloader, tiktok downloader, youtube downloader, facebook video downloader, pinterest downloader, download tiktok without watermark, download youtube videos, free video downloader')">
    <meta name="author" content="Video Downloader">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Free Video Downloader - TikTok, YouTube, Facebook, Pinterest')">
    <meta property="og:description" content="@yield('meta_description', 'Free online video downloader. Download videos from TikTok, YouTube, Facebook, and Pinterest. Fast, free, no registration.')">
    <meta property="og:site_name" content="Video Downloader">
    <meta property="og:locale" content="en_US">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Free Video Downloader')">
    <meta name="twitter:description" content="@yield('meta_description', 'Download videos from TikTok, YouTube, Facebook, and Pinterest for free.')">

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%230f172a'/><path d='M30 65 L50 35 L70 65' stroke='%23ec4899' stroke-width='8' fill='none' stroke-linecap='round' stroke-linejoin='round'/><circle cx='50' cy='28' r='5' fill='%2360a5fa'/></svg>">

    {{-- Structured Data (JSON-LD) --}}
    @yield('structured_data')
    @if(!View::hasSection('structured_data'))
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "Video Downloader",
        "url": "{{ config('app.url') }}",
        "description": "Free online video downloader for TikTok, YouTube, Facebook, and Pinterest.",
        "applicationCategory": "MultimediaApplication",
        "operatingSystem": "Any",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        }
    }
    </script>
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        facebook: { 500: '#1877F2', 600: '#1664d9', 700: '#1151b0' },
                        tiktok:   { 500: '#ff0050', 600: '#e0004a', 700: '#c00040' },
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-950 min-h-screen flex flex-col items-center p-6">

    {{-- Navigation --}}
    <nav class="w-full max-w-xl mb-8">
        <div class="flex items-center justify-center gap-3">
            <a href="{{ route('home') }}"
               class="text-gray-400 hover:text-white text-sm font-medium transition {{ request()->routeIs('home') ? 'text-white' : '' }}">
                Home
            </a>
            <span class="text-gray-600">|</span>
            <a href="{{ route('tiktok.index') }}"
               class="text-gray-400 hover:text-pink-400 text-sm font-medium transition {{ request()->routeIs('tiktok.*') ? 'text-pink-400' : '' }}">
                TikTok
            </a>
            <span class="text-gray-600">|</span>
            <a href="{{ route('youtube.index') }}"
               class="text-gray-400 hover:text-red-400 text-sm font-medium transition {{ request()->routeIs('youtube.*') ? 'text-red-400' : '' }}">
                YouTube
            </a>
            <span class="text-gray-600">|</span>
            <a href="{{ route('facebook.index') }}"
               class="text-gray-400 hover:text-blue-400 text-sm font-medium transition {{ request()->routeIs('facebook.*') ? 'text-blue-400' : '' }}">
                Facebook
            </a>
            <span class="text-gray-600">|</span>
            <a href="{{ route('pinterest.index') }}"
               class="text-gray-400 hover:text-red-400 text-sm font-medium transition {{ request()->routeIs('pinterest.*') ? 'text-red-400' : '' }}">
                Pinterest
            </a>
        </div>
    </nav>

    {{-- Page Content --}}
    <div class="w-full {{ request()->routeIs('admin.*') ? 'max-w-7xl' : 'max-w-xl' }} flex-1 flex items-start justify-center">
        @yield('content')
    </div>

    {{-- Footer with SEO content --}}
    <footer class="mt-12 w-full max-w-xl text-center">
        <div class="border-t border-gray-800 pt-6">
            <p class="text-gray-500 text-xs mb-2">&copy; {{ date('Y') }} Video Downloader &mdash; For personal use only.</p>
            <p class="text-gray-600 text-xs">
                <a href="{{ route('home') }}" class="hover:text-gray-400 transition">Home</a> &middot;
                <a href="{{ route('tiktok.index') }}" class="hover:text-gray-400 transition">TikTok Downloader</a> &middot;
                <a href="{{ route('youtube.index') }}" class="hover:text-gray-400 transition">YouTube Downloader</a> &middot;
                <a href="{{ route('facebook.index') }}" class="hover:text-gray-400 transition">Facebook Downloader</a> &middot;
                <a href="{{ route('pinterest.index') }}" class="hover:text-gray-400 transition">Pinterest Downloader</a>
            </p>
        </div>
    </footer>
</body>
</html>
