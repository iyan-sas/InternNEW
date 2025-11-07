<div class="relative z-10">
    {{-- Flash --}}
    @if (session('success'))
        <p class="text-green-600 text-sm mb-3">{{ session('success') }}</p>
    @endif

    {{-- Add Event button --}}
    <button
        wire:click="create"
        class="bg-blue-600 text-white px-5 py-2 rounded-full hover:bg-blue-700 mt-6 shadow-sm"
    >
        + Add Event
    </button>

    {{-- Modal (Alpine bound to Livewire showModal) --}}
    <div
        x-data="{ open: @entangle('showModal') }"
        x-cloak
        x-show="open"
        x-transition.opacity
        x-trap.noscroll.inert="open"              {{-- lock scroll + trap focus --}}
        class="fixed inset-0 z-[9999]"            {{-- SUPER HIGH so it beats sidebar z-50/70/etc --}}
        aria-modal="true"
        role="dialog"
        aria-labelledby="calendar-event-title"
        style="display: none;"
    >
        {{-- Overlay (on top of page, below the card) --}}
        <div
            class="absolute inset-0 bg-black/40 backdrop-blur-[1px] z-[0]"
            @click="open = false"
        ></div>

        {{-- Dialog layer --}}
        <div class="absolute inset-0 z-[10] flex items-center justify-center p-3 sm:p-4">
            <!-- Curvy panel -->
            <div
                @keydown.escape.window="open = false"
                @click.outside="open = false"
                class="mx-auto flex flex-col bg-white dark:bg-zinc-900 border border-slate-200/70 dark:border-zinc-700/60
                       ring-1 ring-blue-500/10 rounded-3xl overflow-hidden shadow-2xl w-[min(92vw,700px)]"
                style="border-radius:28px;"
            >
                <!-- Header -->
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-200/70 dark:border-zinc-700/60 bg-white/80 dark:bg-zinc-900/80">
                    <h3 id="calendar-event-title" class="text-base sm:text-lg font-semibold">
                        {{ $event_id ? 'Edit Event' : 'Create Event' }}
                    </h3>
                </div>

                <!-- Body -->
                <div class="p-4 sm:p-6 max-h-[70vh] overflow-y-auto">
                    <form wire:submit.prevent="save" class="space-y-4">
                        {{-- Title --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">Title</label>
                            <input
                                type="text"
                                wire:model.defer="title"
                                class="w-full h-11 rounded-xl border border-gray-300 px-3 text-base
                                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="e.g. Orientation / Meeting"
                            >
                            @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Date & Time --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">Date &amp; Time</label>
                            <input
                                type="datetime-local"
                                wire:model.defer="date"
                                class="w-full h-11 rounded-xl border border-gray-300 px-3 text-base
                                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">Description (optional)</label>
                            <textarea
                                rows="4"
                                wire:model.defer="description"
                                class="w-full rounded-xl border border-gray-300 px-3 py-2 text-base
                                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Short details…"
                            ></textarea>
                            @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Actions --}}
                        <div class="pt-2 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                            <button
                                type="button"
                                @click="open = false"
                                class="w-full sm:w-auto px-5 py-2 rounded-full border border-transparent text-white bg-red-500 hover:bg-red-600"
                            >
                                Cancel
                            </button>

                            @if ($event_id)
                                <button
                                    type="button"
                                    wire:click="delete"
                                    class="w-full sm:w-auto px-5 py-2 rounded-full bg-red-600 text-white hover:bg-red-700"
                                >
                                    Delete
                                </button>
                            @endif

                            <button
                                type="submit"
                                class="w-full sm:w-auto px-6 py-2 rounded-full bg-blue-600 text-white hover:bg-blue-700"
                            >
                                Save
                            </button>
                        </div>
                    </form>
                </div>
                <!-- /Body -->
            </div>
            <!-- /Panel -->
        </div>
    </div>
</div>
