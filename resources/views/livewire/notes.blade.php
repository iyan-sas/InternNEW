<div>
    <div class="space-y-2">
    <textarea wire:model="content" placeholder="Type quick reminders or to-dos here..." class="w-full border rounded p-2"></textarea>

    <div class="flex justify-end">
        <button wire:click="saveNote" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
            Save Notes
        </button>
    </div>

    @foreach ($notes as $note)
        <div class="flex justify-between items-center border p-2 rounded">
            <span>{{ $note->content }}</span>
            <button wire:click="deleteNote({{ $note->id }})" class="text-red-500 text-sm">X</button>
        </div>
    @endforeach
</div>


