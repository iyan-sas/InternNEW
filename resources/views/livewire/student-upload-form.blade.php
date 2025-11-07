<form wire:submit.prevent="upload" class="space-y-3">
    <input type="file" wire:model="document" class="border p-2 rounded w-full">
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Upload</button>
    @if (session()->has('message'))
        <p class="text-green-600">{{ session('message') }}</p>
    @endif
</form>

<h3 class="mt-6 font-semibold">Your Uploads</h3>
<ul class="list-disc list-inside">
    @foreach ($uploads as $upload)
        <li>
            <a href="{{ asset('storage/' . $upload->filename) }}" download>{{ $upload->filename }}</a>
        </li>
    @endforeach
</ul>

