@php
    use Illuminate\Support\Facades\Storage;
@endphp

<div class="border rounded p-4 bg-white">
    <h3 class="text-lg font-semibold mb-3">{{ $title }}</h3>

    @forelse ($documents as $doc)
        <div class="border rounded p-3 mb-2 bg-gray-50">
            <div class="font-medium">{{ $doc->title }}</div>
            <div class="text-xs text-gray-600">
                ID: {{ $doc->id }} |
                Type: {{ $doc->type }} |
                File: {{ $doc->filename }}
            </div>

            <div class="mt-1">
                @if($doc->filename)
                    {{-- Use the streaming route to avoid 403/404 on some servers --}}
                    <a href="{{ route('files.show', $doc->id) }}"
                       target="_blank"
                       class="text-blue-600 underline">
                        🔗 View File
                    </a>
                @else
                    <span class="text-red-600 text-sm">⚠ File missing</span>
                @endif
            </div>
        </div>
    @empty
        <div class="italic text-gray-500">No documents available.</div>
    @endforelse
</div>
