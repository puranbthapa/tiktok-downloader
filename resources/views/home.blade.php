{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('title', 'Video Downloader')

@section('content')
<div class="w-full">
    <div class="text-center mb-10">
        <h1 class="text-4xl font-bold text-white mb-2">Video Downloader</h1>
        <p class="text-gray-400">Download videos from your favourite platforms</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- TikTok Card --}}
        <a href="{{ route('tiktok.index') }}"
           class="group bg-gray-800 border border-gray-700 hover:border-pink-500/50 rounded-2xl p-6 text-center transition-all hover:scale-[1.02]">
            <div class="flex justify-center mb-4">
                <svg class="w-14 h-14" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M34.1451 0H26.0556V32.6956C26.0556 36.5913 22.9444 39.7913 19.0726 39.7913C15.2007 39.7913 12.0895 36.5913 12.0895 32.6956C12.0895 28.8696 15.1311 25.7391 18.8638 25.6V17.3913C10.6347 17.5304 4 24.2783 4 32.6956C4 41.1826 10.7743 48 19.1422 48C27.5101 48 34.2844 41.1131 34.2844 32.6956V15.9304C37.3956 18.1565 41.1284 19.4783 45 19.5478V11.3391C38.9157 11.0609 34.1451 6.0522 34.1451 0Z" fill="#EE1D52"/>
                    <path d="M34.1451 0H26.0556V32.6956C26.0556 36.5913 22.9444 39.7913 19.0726 39.7913C15.2007 39.7913 12.0895 36.5913 12.0895 32.6956C12.0895 28.8696 15.1311 25.7391 18.8638 25.6V17.3913C10.6347 17.5304 4 24.2783 4 32.6956C4 41.1826 10.7743 48 19.1422 48C27.5101 48 34.2844 41.1131 34.2844 32.6956V15.9304C37.3956 18.1565 41.1284 19.4783 45 19.5478V11.3391C38.9157 11.0609 34.1451 6.0522 34.1451 0Z" fill="url(#tiktok_grad)"/>
                    <defs>
                        <linearGradient id="tiktok_grad" x1="4" y1="0" x2="45" y2="48" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#25F4EE"/>
                            <stop offset="1" stop-color="#FE2C55"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-white mb-1 group-hover:text-pink-400 transition">TikTok</h2>
            <p class="text-gray-400 text-sm">Download TikTok videos without watermark</p>
        </a>

        {{-- YouTube Card --}}
        <a href="{{ route('youtube.index') }}"
           class="group bg-gray-800 border border-gray-700 hover:border-red-500/50 rounded-2xl p-6 text-center transition-all hover:scale-[1.02]">
            <div class="flex justify-center mb-4">
                <svg class="w-14 h-14" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M44.522 12.415a5.556 5.556 0 0 0-3.91-3.934C37.248 7.5 24 7.5 24 7.5s-13.248 0-16.612.981a5.556 5.556 0 0 0-3.91 3.934C2.5 15.793 2.5 24 2.5 24s0 8.207.978 11.585a5.556 5.556 0 0 0 3.91 3.934C10.752 40.5 24 40.5 24 40.5s13.248 0 16.612-.981a5.556 5.556 0 0 0 3.91-3.934C45.5 32.207 45.5 24 45.5 24s0-8.207-.978-11.585Z" fill="#FF0000"/>
                    <path d="M19.5 31.5V16.5L33 24l-13.5 7.5Z" fill="#FFF"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-white mb-1 group-hover:text-red-400 transition">YouTube</h2>
            <p class="text-gray-400 text-sm">Download YouTube videos & shorts</p>
        </a>

        {{-- Facebook Card --}}
        <a href="{{ route('facebook.index') }}"
           class="group bg-gray-800 border border-gray-700 hover:border-blue-500/50 rounded-2xl p-6 text-center transition-all hover:scale-[1.02]">
            <div class="flex justify-center mb-4">
                <svg class="w-14 h-14" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M48 24C48 10.745 37.255 0 24 0S0 10.745 0 24c0 11.98 8.776 21.908 20.25 23.708V30.938h-6.094V24h6.094v-5.288c0-6.014 3.583-9.337 9.065-9.337 2.626 0 5.372.469 5.372.469v5.906h-3.026c-2.981 0-3.911 1.85-3.911 3.75V24h6.656l-1.064 6.938H27.75v16.77C39.224 45.908 48 35.98 48 24Z" fill="#1877F2"/>
                    <path d="M33.342 30.938 34.406 24H27.75v-4.5c0-1.9.93-3.75 3.911-3.75h3.026V9.844s-2.746-.469-5.372-.469c-5.482 0-9.065 3.323-9.065 9.337V24h-6.094v6.938h6.094v16.77a24.174 24.174 0 0 0 7.5 0V30.938h5.592Z" fill="#fff"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-white mb-1 group-hover:text-blue-400 transition">Facebook</h2>
            <p class="text-gray-400 text-sm">Download Facebook reels & videos</p>
        </a>

        {{-- Pinterest Card --}}
        <a href="{{ route('pinterest.index') }}"
           class="group bg-gray-800 border border-gray-700 hover:border-red-500/50 rounded-2xl p-6 text-center transition-all hover:scale-[1.02]">
            <div class="flex justify-center mb-4">
                <svg class="w-14 h-14" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M24 0C10.746 0 0 10.746 0 24c0 10.168 6.326 18.863 15.258 22.354-.21-1.902-.4-4.82.084-6.898.437-1.875 2.822-11.963 2.822-11.963s-.72-1.443-.72-3.576c0-3.35 1.943-5.854 4.363-5.854 2.058 0 3.052 1.545 3.052 3.398 0 2.07-1.317 5.164-1.997 8.034-.568 2.4 1.204 4.357 3.572 4.357 4.287 0 7.583-4.52 7.583-11.04 0-5.773-4.149-9.81-10.076-9.81-6.862 0-10.89 5.147-10.89 10.468 0 2.074.798 4.296 1.794 5.505a.72.72 0 0 1 .166.69c-.183.76-.59 2.4-.67 2.735-.105.442-.35.535-.808.322-3.015-1.404-4.899-5.81-4.899-9.348 0-7.612 5.531-14.604 15.948-14.604 8.374 0 14.88 5.964 14.88 13.937 0 8.314-5.244 15.01-12.524 15.01-2.446 0-4.746-1.27-5.533-2.77 0 0-1.211 4.61-1.505 5.74-.545 2.1-2.017 4.732-3.001 6.337A24.01 24.01 0 0 0 24 48c13.254 0 24-10.746 24-24S37.254 0 24 0Z" fill="#E60023"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-white mb-1 group-hover:text-red-400 transition">Pinterest</h2>
            <p class="text-gray-400 text-sm">Download Pinterest videos & pins</p>
        </a>
    </div>
</div>
@endsection
