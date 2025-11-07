<div class="p-4 bg-white rounded shadow space-y-4">
    <form wire:submit.prevent="addStudent" class="space-y-3">
        <input type="text" wire:model="student_name" placeholder="Student Name" class="w-full border p-2 rounded">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Generate Upload Link</button>
    </form>

    @if (session()->has('message'))
        <div class="text-green-600">{{ session('message') }}</div>
    @endif

    <h3 class="font-semibold mt-6">Generated Links:</h3>
    <ul class="list-disc list-inside">
        @foreach ($invites as $invite)
            <li>
                {{ $invite->student_name }} – 
                <a href="{{ url('/student-upload/' . $invite->token) }}" class="text-blue-600 underline" target="_blank">
                    {{ url('/student-upload/' . $invite->token) }}
                </a>
            </li>
        @endforeach
    </ul>
</div>

