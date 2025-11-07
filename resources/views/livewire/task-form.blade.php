{{-- resources/views/livewire/task-form.blade.php --}}
<div class="border border-gray-200 rounded-lg bg-white p-4 shadow-sm"
     x-data="{ open: false }">

    {{-- Header + Toggle --}}
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-base font-semibold text-gray-900">Tasks</h2>

        <button type="button"
                @click="
                    open = !open;
                    if (open) { $nextTick(() => $refs.title?.focus()) }
                "
                class="px-3 py-1 text-sm rounded bg-blue-600 text-white hover:bg-blue-700">
            <span x-show="!open">+ Create Task</span>
            <span x-show="open">× Close</span>
        </button>
    </div>

    {{-- Collapsible form (hidden until opened) --}}
    <div x-show="open" x-cloak x-transition.opacity.duration.150ms class="mt-3 space-y-3">

        {{-- Title --}}
        <div>
            <input type="text"
                   x-ref="title"
                   wire:model.defer="title"
                   placeholder="Title"
                   class="w-full border rounded p-2">
            @error('title')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Instruction --}}
        <div>
            <textarea wire:model.defer="instruction"
                      placeholder="Instruction"
                      rows="4"
                      class="w-full border rounded p-2"></textarea>
            @error('instruction')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- 📅 Due Date --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
            <input type="date"
                   wire:model.defer="due_date"
                   min="{{ now()->toDateString() }}"
                   class="w-full border rounded p-2">
            @error('due_date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="pt-1">
            <button wire:click="createTask"
                    class="px-4 py-2 rounded bg-green-500 text-white hover:bg-green-500"
                    wire:loading.attr="disabled">
                Save Task
            </button>
        </div>
    </div>

    {{-- Keep x-cloak CSS INSIDE the same root to avoid multiple roots --}}
    <style>[x-cloak]{display:none!important}</style>
</div>
