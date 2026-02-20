{{-- resources/views/pinterest/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Pinterest Video Downloader')

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
</div>
@endsection
