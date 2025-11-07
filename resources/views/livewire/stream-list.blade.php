<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
    @forelse ($streams as $stream)
        <div class="bg-white dark:bg-zinc-700 p-4 rounded-xl shadow-md">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">
                College: {{ $stream->title }}
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                Section: {{ $stream->description }}
            </p>
            <span class="text-xs text-gray-500">Token: {{ $stream->stream_token }}</span>
        </div>
    @empty
        <p class="text-gray-500 col-span-full text-center">No classes available.</p>
    @endforelse
</div>


