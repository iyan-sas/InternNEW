<div>
    @if (session()->has('success'))
        <div class="text-green-600 mb-2">{{ session('success') }}</div>
    @endif

    <form wire:submit.prevent="upload">
        <input type="file" wire:model="file" class="mb-2">
        @error('file') <span class="text-red-600">{{ $message }}</span> @enderror
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Upload</button>
    </form>

    <h3 class="mt-4 font-bold">Uploaded Files:</h3>
    <ul>
        @foreach ($documents as $doc)
            <li>{{ $doc->file_name }}</li>
        @endforeach
    </ul>
</div>
