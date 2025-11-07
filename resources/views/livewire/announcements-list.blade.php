@php
    use App\Enums\UserRole;
@endphp

<div class="relative space-y-4">
    {{-- Overlay to close any open kebab menu (click anywhere) --}}
    @if ($menuOpenId)
        <button type="button" wire:click="closeMenus" class="fixed inset-0 z-10 cursor-default" aria-hidden="true"></button>
    @endif

    {{-- Flash --}}
    @if (session('success'))
        <p class="text-green-600">{{ session('success') }}</p>
    @endif

    {{-- Announcements list --}}
    @forelse ($announcements as $a)
        @php
            $role = auth()->user()->role ?? null;
            $canManage = auth()->id() === $a->user_id
                || (is_string($role) && in_array(strtolower($role), ['admin','coordinator']))
                || ($role instanceof UserRole && in_array($role, [UserRole::Admin, UserRole::Coordinator], true));
        @endphp

        <div class="border rounded px-3 py-2 bg-white" wire:key="a-{{ $a->id }}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    {{-- View mode --}}
                    @if ($editingId !== $a->id)
                        <p class="text-gray-900 break-words whitespace-pre-line">{{ $a->message }}</p>
                    @endif

                    {{-- Edit mode --}}
                    @if ($editingId === $a->id)
                        <div class="mt-2 space-y-2">
                            <textarea
                                wire:model.defer="editingText"
                                rows="2"
                                class="w-full border rounded px-2 py-1 text-sm"
                            ></textarea>

                            <div class="flex gap-2">
                                <button
                                    wire:click="updateAnnouncement"
                                    class="px-3 py-1 rounded bg-blue-600 text-white text-sm"
                                >Save</button>

                                <button
                                    wire:click="cancelEdit"
                                    class="px-3 py-1 rounded border text-sm"
                                >Cancel</button>
                            </div>

                            @error('editingText')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <p class="text-xs text-gray-500 mt-1">
                        Posted by {{ $a->user->name ?? 'Coordinator' }} • {{ optional($a->created_at)->diffForHumans() }}
                    </p>
                </div>

                {{-- ⋮ Kebab menu (same structure as your working comments kebab) --}}
                @if ($canManage && $editingId !== $a->id)
                    <div class="relative shrink-0 z-20">
                        <button
                            type="button"
                            class="p-1 rounded hover:bg-gray-100"
                            wire:click="toggleMenu({{ $a->id }})"
                            aria-haspopup="menu"
                            aria-expanded="{{ $menuOpenId === $a->id ? 'true' : 'false' }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 6.75a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3z"/>
                            </svg>
                        </button>

                        @if ($menuOpenId === $a->id)
                            <div class="absolute right-0 mt-2 w-32 rounded border bg-white shadow-lg">
                                <button
                                    type="button"
                                    wire:click="startEdit({{ $a->id }})"
                                    class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50"
                                >Edit</button>

                                <button
                                    type="button"
                                    wire:click="askDelete({{ $a->id }})"
                                    class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-gray-50"
                                >Delete</button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Comments below each announcement --}}
            <div class="mt-3">
                @livewire('announcement-comments', ['announcement' => $a], key('ac-'.$a->id))
            </div>
        </div>
    @empty
        <div class="bg-white border rounded p-4">
            <p class="text-gray-700">No announcements yet.</p>
        </div>
    @endforelse

    {{-- Delete confirmation modal (same layering as comments) --}}
    @if ($confirmDeleteId)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-2xl shadow-2xl text-center p-6 w-[420px] max-w-[92vw] mx-4">
                <h2 class="text-lg font-semibold text-zinc-900 mb-2">Delete this announcement?</h2>
                <p class="text-sm text-zinc-600 mb-4">This action cannot be undone.</p>

                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        wire:click="cancelDelete"
                        class="px-4 py-2 rounded-lg bg-red-500 hover:bg-zinc-200 text-white"
                    >Cancel</button>

                    <button
                        type="button"
                        wire:click="confirmDelete"
                        class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white"
                    >Yes</button>
                </div>
            </div>
        </div>
    @endif
</div>
