{{-- Event List + Edit Modal + Responsive Delete Confirm --}}
<div
    class="mt-6 bg-white border rounded-3xl shadow p-4 md:p-5 ring-1 ring-black/5 relative overflow-hidden"
    x-data="{
        confirm:{ open:false, id:null },
        openConfirm(id){ this.confirm={open:true, id}; },
        closeConfirm(){ this.confirm={open:false, id:null}; }
    }"
>
    <div class="mb-2 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="h-6 w-6 rounded-md bg-blue-50 text-blue-600 grid place-items-center ring-1 ring-blue-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a3 3 0 0 1 3 3v11a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h1V3a1 1 0 0 1 1-1Zm13 9H4v8a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-8ZM5 7a1 1 0 0 0-1 1v1h16V8a1 1 0 0 0-1-1H5Z"/>
                </svg>
            </div>
            <h2 class="text-lg font-semibold">Upcoming Events</h2>
        </div>
    </div>

    {{-- success flash (auto-hide) --}}
    @if (session('success'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 2500)"
            x-show="show"
            x-transition.opacity.duration.300ms
            class="mb-3 rounded-full border border-green-200 bg-green-50 px-3 py-2 text-xs text-green-800"
            role="alert"
        >
            {{ session('success') }}
        </div>
    @endif

    @if (collect($events)->isEmpty())
        <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-6 text-center">
            <p class="text-sm text-zinc-600">No events scheduled.</p>
            <p class="text-xs text-zinc-500 mt-1">
                Click <span class="font-medium text-blue-600">+ Add Event</span> to create your first one.
            </p>
        </div>
    @else
        <ul class="divide-y divide-gray-200">
            @foreach ($events as $event)
                @php
                    $isOwner = auth()->id() === $event->user_id;

                    try {
                        $formatted = $event->date
                            ? \Carbon\Carbon::parse($event->date)->timezone(config('app.timezone'))->format('M d, Y · g:i A')
                            : null;
                    } catch (\Throwable $t) { $formatted = null; }

                    $roleRaw   = optional($event->user)->role;
                    $roleStr   = is_string($roleRaw) ? $roleRaw
                               : (is_object($roleRaw)
                                    ? (method_exists($roleRaw, 'value') ? $roleRaw->value : (property_exists($roleRaw, 'name') ? $roleRaw->name : (string)$roleRaw))
                                    : null);
                    $roleLower = $roleStr ? strtolower($roleStr) : null;

                    $badgeClasses = $roleLower === 'admin'
                        ? 'border-rose-300 bg-rose-50 text-rose-700'
                        : 'border-blue-300 bg-blue-50 text-blue-700';

                    $displayName = optional($event->user)->name;
                @endphp

                <li class="py-3 px-2">
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4 rounded-2xl hover:bg-slate-50 transition"
                        wire:key="evt-{{ $event->id }}"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-medium text-gray-900 truncate">{{ $event->title }}</p>

                                @if ($displayName)
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs {{ $badgeClasses }}">
                                        {{ $displayName }}
                                        @if ($roleLower)
                                            <span class="opacity-75">({{ $roleLower }})</span>
                                        @endif
                                    </span>
                                @endif
                            </div>

                            @if ($formatted)
                                <p class="mt-0.5 text-sm text-gray-600">{{ $formatted }}</p>
                            @endif

                            @if ($event->description)
                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $event->description }}</p>
                            @endif
                        </div>

                        {{-- Owner actions --}}
                        @if ($isOwner)
                            <div class="shrink-0 flex items-center gap-2 sm:pt-1">
                                <button
                                    type="button"
                                    class="h-9 px-3 rounded-full text-sm font-medium border border-zinc-300 hover:bg-zinc-50"
                                    wire:click="edit({{ $event->id }})"
                                    title="Edit"
                                >Edit</button>

                                <button
                                    type="button"
                                    class="h-9 px-3 rounded-full text-sm font-medium bg-red-50 text-red-600 border border-red-200 hover:bg-red-100"
                                    @click="openConfirm({{ $event->id }})"
                                    title="Delete"
                                >Delete</button>
                            </div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    {{-- =========================
         Edit Modal (full-screen)
       ========================= --}}
    @if ($showEditModal)
        <div class="fixed inset-0 z-[9998] bg-black/40 backdrop-blur-[1px]" aria-hidden="true"></div>
        <div
            class="fixed inset-0 z-[9999] flex items-center justify-center p-3 sm:p-4"
            role="dialog" aria-modal="true" aria-labelledby="edit-event-title"
        >
            <div class="w-[min(92vw,700px)] bg-white rounded-[24px] md:rounded-[28px] overflow-hidden shadow-2xl border border-slate-200 ring-1 ring-blue-500/10">
                <div class="px-5 sm:px-6 py-3 sm:py-4 border-b border-slate-200 bg-white/90">
                    <h3 id="edit-event-title" class="text-base sm:text-lg font-semibold">Edit Event</h3>
                </div>
                <div class="p-4 sm:p-6 space-y-4 max-h-[70vh] overflow-y-auto" style="scrollbar-gutter: stable; overscroll-behavior: contain;">
                    <div>
                        <label class="block text-sm font-medium mb-1">Title</label>
                        <input type="text" wire:model.defer="title" class="w-full h-11 rounded-xl border border-gray-300 px-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Date &amp; Time</label>
                        <input type="datetime-local" wire:model.defer="dateLocal" class="w-full h-11 rounded-xl border border-gray-300 px-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('dateLocal') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Description (optional)</label>
                        <textarea rows="4" wire:model.defer="description" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="px-5 sm:px-6 py-4 border-t border-slate-200 flex flex-col sm:flex-row sm:justify-end gap-2">
                    <button type="button" class="w-full sm:w-auto px-6 py-2 text-sm rounded-full bg-blue-600 text-white hover:bg-blue-700" wire:click="update">Save Changes</button>
                    <button type="button" class="w-full sm:w-auto px-5 py-2 rounded-full border border-transparent text-white bg-red-500 hover:bg-red-600" wire:click="cancelEdit">Cancel</button>
                </div>
            </div>
        </div>
    @endif

    {{-- =========================
         Delete Confirm (constrained to card)
       ========================= --}}
    <div
        x-show="confirm.open"
        x-cloak
        x-transition.opacity
        x-trap.noscroll.inert="confirm.open"
        class="absolute inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-title"
        @keydown.escape.window="closeConfirm()"
        style="display:none;"
    >
        {{-- Overlay limited to the card --}}
        <div class="absolute inset-0 bg-black/30 backdrop-blur-[1px] rounded-3xl" @click="closeConfirm()"></div>

        {{-- Card --}}
        <div class="relative z-10 w-[min(92%,420px)] rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 border border-slate-200">
            <div class="px-5 py-4">
                <h3 id="confirm-title" class="text-lg font-semibold">Are you sure you want to delete this?</h3>
                <p class="mt-1 text-sm text-zinc-600">This action cannot be undone.</p>
            </div>

            <div class="px-5 pb-5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <button
                    type="button"
                    class="w-full sm:w-auto h-10 px-4 rounded-full bg-blue-600 text-white font-medium hover:bg-blue-700"
                    @click="closeConfirm()"
                >Cancel</button>

                <button
                    type="button"
                    class="w-full sm:w-auto h-10 px-4 rounded-full bg-red-500 text-white font-medium hover:bg-red-600"
                    @click="$wire.delete(confirm.id); closeConfirm();"
                >Yes</button>
            </div>
        </div>
    </div>
</div>
