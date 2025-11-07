<div class="relative space-y-3">
    {{-- Overlay to close any open kebab menu (click anywhere) --}}
    @if ($menuOpenId)
        <button type="button" wire:click="closeMenus" class="fixed inset-0 z-10 cursor-default" aria-hidden="true"></button>
    @endif

    {{-- Flash --}}
    @if (session('success'))
        <p class="text-green-600">{{ session('success') }}</p>
    @endif

    {{-- Comments list --}}
    @foreach ($comments as $c)
        @php
            $canManage = auth()->id() === $c->user_id
                || in_array(auth()->user()->role, [\App\Enums\UserRole::Admin, \App\Enums\UserRole::Coordinator]);
        @endphp

        <div class="border rounded px-3 py-2 bg-white" wire:key="c-{{ $c->id }}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 w-full">
                    <p class="text-sm">
                        <span class="font-medium">{{ $c->user->name }}</span>
                        <span class="text-xs text-gray-500">• {{ $c->created_at->diffForHumans() }}</span>
                    </p>

                    {{-- View mode --}}
                    @if ($editingId !== $c->id)
                        <p class="text-gray-800 text-sm break-words whitespace-pre-line">{{ $c->comment }}</p>
                    @endif

                    {{-- Edit mode --}}
                    @if ($editingId === $c->id)
                        <div class="mt-2 space-y-2">
                            <textarea
                                wire:model.defer="editingText"
                                rows="2"
                                class="w-full border rounded px-2 py-1 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            ></textarea>

                            {{-- Responsive actions: stack on mobile, inline on sm+ --}}
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                <button
                                    wire:click="updateComment"
                                    class="w-full sm:w-auto px-3 py-2 rounded bg-blue-600 text-white text-sm hover:bg-blue-700"
                                >Save</button>

                                <button
                                    wire:click="cancelEdit"
                                    class="w-full sm:w-auto px-3 py-2 rounded border text-sm hover:bg-gray-50"
                                >Cancel</button>
                            </div>

                            @error('editingText')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                {{-- ⋮ Kebab menu --}}
                @if ($canManage && $editingId !== $c->id)
                    <div class="relative shrink-0 z-20">
                        <button
                            type="button"
                            class="p-1 rounded hover:bg-gray-100"
                            wire:click="toggleMenu({{ $c->id }})"
                            aria-haspopup="menu"
                            aria-expanded="{{ $menuOpenId === $c->id ? 'true' : 'false' }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 6.75a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3z"/>
                            </svg>
                        </button>

                        @if ($menuOpenId === $c->id)
                            <div class="absolute right-0 mt-2 w-36 sm:w-40 rounded border bg-white shadow-lg">
                                <button
                                    type="button"
                                    wire:click="startEdit({{ $c->id }})"
                                    class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50"
                                >Edit</button>

                                <button
                                    type="button"
                                    wire:click="askDelete({{ $c->id }})"
                                    class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-gray-50"
                                >Delete</button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endforeach

    {{-- New comment (responsive row) --}}
    <form wire:submit.prevent="postComment" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
        <input
            type="text"
            wire:model.defer="comment"
            class="flex-1 min-w-0 w-full border rounded px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
            placeholder="Add class comment…"
        >
        <button
            type="submit"
            class="w-full sm:w-auto px-4 py-2 rounded bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700"
        >Post Comment</button>
    </form>
    @error('comment')
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror

    {{-- Delete confirmation modal --}}
    @if ($confirmDeleteId)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white rounded-2xl shadow-2xl text-center p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold text-zinc-900 mb-2">Delete this comment?</h2>
                <p class="text-sm text-zinc-600 mb-4">This action cannot be undone.</p>

                <div class="flex flex-col sm:flex-row justify-end gap-3">
                    <button
                        type="button"
                        wire:click="cancelDelete"
                        class="w-full sm:w-auto px-4 py-2 rounded-lg border hover:bg-zinc-50"
                    >Cancel</button>

                    <button
                        type="button"
                        wire:click="confirmDelete"
                        class="w-full sm:w-auto px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white"
                    >Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
