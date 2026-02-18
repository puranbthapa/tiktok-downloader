{{-- resources/views/tiktok/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TikTok Downloader</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-xl">
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
    </div>
</body>
</html>
