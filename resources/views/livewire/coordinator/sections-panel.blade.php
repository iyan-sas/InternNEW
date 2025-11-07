{{-- resources/views/livewire/coordinator/sections-panel.blade.php --}}
@php
    use App\Models\SchoolYear;

    $bgColors = [
        ['bg' => '#DBEAFE', 'header' => '#BFDBFE', 'border' => 'border-blue-400'],
        ['bg' => '#BBF7D0', 'header' => '#86EFAC', 'border' => 'border-green-400'],
        ['bg' => '#FEF9C3', 'header' => '#FDE68A', 'border' => 'border-yellow-400'],
        ['bg' => '#FBCFE8', 'header' => '#F9A8D4', 'border' => 'border-pink-400'],
        ['bg' => '#E9D5FF', 'header' => '#D8B4FE', 'border' => 'border-purple-400'],
        ['bg' => '#FECACA', 'header' => '#FCA5A5', 'border' => 'border-red-400'],
    ];

    // Normalize current user's role (string or enum)
    $roleRaw = auth()->user()?->role;
    $roleStr = is_object($roleRaw) && property_exists($roleRaw, 'value')
        ? strtolower($roleRaw->value)
        : strtolower((string) $roleRaw);
    $isAdmin = ($roleStr === 'admin');

    // Build invite URL (only used/shown to admins)
    $inviteUrl = !empty($stream->coordinator_invite_token ?? null)
        ? route('coordinator.join', ['token' => $stream->coordinator_invite_token])
        : null;

    // Admin-controlled active School Year (for display in Create modal)
    $activeSy = SchoolYear::current()?->label;
@endphp

<div
    x-data="{ confirm:{show:false,action:'',id:null}, copiedInvite:false }"
    x-on:section-confirm.window="
        confirm.action = $event.detail.action;
        confirm.id     = $event.detail.id;
        confirm.show   = true;
    "
    x-init="
        // Cross-tab: when any tab writes to localStorage('sy:active'), refresh
        window.addEventListener('storage', (e) => {
            if (e.key === 'sy:active') { $wire.refreshFromSyChange() }
        });

        // Same-tab: when SchoolYearSettings dispatches this browser event, refresh
        window.addEventListener('sy-changed', () => { $wire.refreshFromSyChange() });
    "
>
    <style>[x-cloak]{display:none!important}</style>

    @if (session('success'))
        <div class="mb-3 text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-3 text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Header + (optional) Create --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm mb-4">
        <div class="px-5 py-4 border-b bg-slate-50/60 rounded-t-xl flex items-center justify-between">
            <h3 class="font-semibold text-slate-900">Sections (Coordinator &amp; Section)</h3>

            @if(!isset($canCreate) || $canCreate)
                <button type="button"
                        wire:click="open"
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">
                    + Create
                </button>
            @endif
        </div>

        <div class="p-4 md:p-5 space-y-4">
            {{-- Coordinator Self-Create Invite Link (ADMIN ONLY) --}}
            @if($isAdmin)
                <div class="rounded-lg border border-slate-200 p-4 bg-slate-50/50">
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div class="min-w-[220px]">
                            <p class="text-sm font-medium text-slate-800">Coordinator Self-Create Link</p>
                            <p class="text-xs text-slate-500">
                                Share this link to let the coordinator create the section themselves.
                            </p>
                        </div>

                        <div class="flex-1 min-w-[260px]">
                            @if ($inviteUrl)
                                <div class="flex items-center gap-2">
                                    <input type="text" readonly value="{{ $inviteUrl }}"
                                           class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <button type="button"
                                            class="shrink-0 px-3 py-2 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700"
                                            @click="navigator.clipboard.writeText('{{ $inviteUrl }}'); copiedInvite=true; setTimeout(()=>copiedInvite=false,1500)">
                                        Copy
                                    </button>
                                    <a href="{{ $inviteUrl }}" target="_blank" rel="noopener"
                                       class="shrink-0 px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-700 hover:bg-slate-100">
                                        Open
                                    </a>
                                </div>
                                <span x-show="copiedInvite" x-transition class="mt-1 inline-block text-xs text-green-600">Copied!</span>
                            @else
                                <div class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-3 py-2">
                                    No <code>coordinator_invite_token</code> found for this class.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Sections Grid --}}
            @if ($sections->isEmpty())
                <p class="text-sm text-slate-500">No sections yet.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach ($sections as $i => $s)
                        @php
                            $colors  = $bgColors[$i % count($bgColors)];
                            $openUrl = route('section.show', ['stream' => $stream->id, 'section' => $s->id]);
                        @endphp

                        <div
                            wire:key="section-{{ $s->id }}"
                            x-data="{ menu:false }"
                            class="relative rounded-lg border-2 border-blue-500 shadow-sm ring-2 ring-blue-500/20 ring-offset-1 ring-offset-white transition-all duration-150 hover:ring-blue-600/30 focus-within:ring-blue-600/30 {{ $colors['border'] }}"
                            style="background-color: {{ $colors['bg'] }};"
                        >
                            {{-- Card header --}}
                            <div class="px-4 py-2 rounded-t-lg flex items-center justify-between border-b border-blue-300/50"
                                 style="background-color: {{ $colors['header'] }};">
                                <div class="w-full">
                                    <div class="flex items-baseline gap-2">
                                        <a href="{{ $openUrl }}" class="block font-bold text-base sm:text-lg hover:underline">
                                            {{ $s->section_name }}
                                        </a>

                                        {{-- Show pill if set; otherwise show subtle placeholder --}}
                                        @if(filled($s->school_year))
                                            <span class="text-[11px] sm:text-xs font-medium px-2 py-0.5 rounded-full bg-zinc-100 text-zinc-700">
                                                SY {{ $s->school_year_display ?? str_replace('-', '–', $s->school_year) }}
                                            </span>
                                        @else
                                            <span class="text-[11px] sm:text-xs font-medium px-2 py-0.5 rounded-full bg-zinc-50 text-zinc-400">
                                                SY —
                                            </span>
                                        @endif
                                    </div>

                                    <p class="text-sm text-black/80">
                                        Coordinator:
                                        <span class="font-medium">{{ $s->coordinator?->name ?? $s->coordinator_name ?? '—' }}</span>
                                    </p>
                                </div>

                                @if(!isset($canManage) || $canManage)
                                    <div class="relative ml-2" @click.outside="menu=false">
                                        <button type="button" @click.stop="menu=!menu"
                                                class="text-black text-2xl leading-none px-2 rounded-md hover:bg-black/5">
                                            &#x22EE;
                                        </button>

                                        <div x-cloak x-show="menu" x-transition
                                             @click.stop
                                             class="absolute right-0 mt-2 w-32 bg-white border border-gray-200 rounded shadow-lg z-10">
                                            <button type="button"
                                                    @click.prevent="
                                                        menu=false;
                                                        window.dispatchEvent(new CustomEvent('section-confirm', {
                                                            detail: { action: 'edit', id: {{ $s->id }} }
                                                        }))
                                                    "
                                                    class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">
                                                Edit
                                            </button>
                                            <button type="button"
                                                    @click.prevent="
                                                        menu=false;
                                                        window.dispatchEvent(new CustomEvent('section-confirm', {
                                                            detail: { action: 'delete', id: {{ $s->id }} }
                                                        }))
                                                    "
                                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Body --}}
                            <div class="p-4 text-black">
                                <p class="text-sm text-gray-700">Click the section name above to open details.</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- CREATE MODAL --}}
    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 z-40"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" wire:key="create-modal">
            <div class="rounded-2xl border bg-white shadow-2xl mx-auto"
                 role="dialog" aria-modal="true" aria-labelledby="create-section-title"
                 style="width:680px; max-width:92vw;"
                 x-data
                 x-init="$nextTick(() => { setTimeout(() => { $refs.sectionInput?.focus() }, 0) })"
            >
                <div class="px-6 py-4 border-b">
                    <h2 id="create-section-title" class="text-lg font-semibold">Create Section</h2>
                </div>
                <div class="p-6">
                    <form wire:submit.prevent="create" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Section</label>
                            <input
                                x-ref="sectionInput"
                                type="text"
                                wire:model.defer="section_name"
                                class="w-full h-11 rounded-md border-gray-300 px-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="e.g., BSIT 4F"
                                autofocus
                            >
                            @error('section_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- School Year (Admin-controlled, read-only) --}}
                        <div>
                            <div class="block text-sm font-medium mb-1">School Year</div>
                            <div class="px-3 py-2 h-11 flex items-center rounded-md border bg-slate-50 text-sm">
                                {{ $activeSy ?? '— (not set by admin)' }}
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Set by admin • applied automatically to this section.
                            </p>
                        </div>

                        {{-- Coordinator (auto; read-only) --}}
                        <div>
                            <div class="block text-sm font-medium mb-1">Coordinator (auto)</div>
                            <div class="px-3 py-2 h-11 flex items-center rounded-md border bg-slate-50 text-sm">
                                {{ $coordinatorName ?? '—' }}
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Automatically set to the signed-in coordinator and saved with the section.
                            </p>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" wire:click="cancel"
                                    class="px-5 py-2.5 bg-red-500 text-white rounded-md hover:bg-red-600">
                                Cancel
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                    class="px-5 py-2.5 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-60">
                                <span wire:loading.remove>Create</span>
                                <span wire:loading>Creating…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- EDIT MODAL (SY read-only in coordinator panel) --}}
    @if ($showEditModal ?? false)
        <div class="fixed inset-0 bg-black/40 z-40"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" wire:key="edit-modal">
            <div class="rounded-2xl border bg-white shadow-2xl mx-auto"
                 role="dialog" aria-modal="true" aria-labelledby="edit-section-title"
                 style="width:640px; max-width:92vw;"
                 x-data
                 x-init="$nextTick(() => { setTimeout(() => { $refs.editSectionInput?.focus() }, 0) })"
            >
                <div class="px-6 py-4 border-b">
                    <h2 id="edit-section-title" class="text-lg font-semibold">Edit Section</h2>
                </div>
                <div class="p-6">
                    <form wire:submit.prevent="update" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Section</label>
                            <input
                                x-ref="editSectionInput"
                                type="text"
                                wire:model.defer="edit_section_name"
                                class="w-full h-11 rounded-md border-gray-300 px-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('edit_section_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- School Year (read-only in this panel) --}}
                        <div>
                            <div class="block text-sm font-medium mb-1">School Year</div>
                            <div class="px-3 py-2 h-11 flex items-center rounded-md border bg-slate-50 text-sm">
                                {{ $edit_school_year ?: '—' }}
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                School Year is set by Admin and applied automatically when creating sections.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Coordinator (display name)</label>
                            <input type="text" wire:model.defer="edit_coordinator_name"
                                   class="w-full h-11 rounded-md border-gray-300 px-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('edit_coordinator_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            <p class="text-xs text-slate-500 mt-1">
                                The bound owner remains the original coordinator; this updates only the displayed name.
                            </p>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" wire:click="cancelEdit"
                                    class="px-5 py-2.5 bg-red-500 text-white rounded-md hover:bg-zinc-200">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- TELEPORTED CONFIRM MODAL --}}
    <template x-teleport="body">
        <div x-show="confirm.show" x-transition.opacity.duration.150ms
             class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40">
            <div x-show="confirm.show" x-transition.scale.origin.center.duration.150ms
                 class="bg-white rounded-2xl shadow-2xl text-center p-6 w-[420px] max-w-[92vw] mx-4">
                <h2 class="text-lg font-semibold text-zinc-900 mb-2"
                    x-text="confirm.action==='edit' ? 'Are you sure you want to edit this?' : 'Are you sure you want to delete this?'"></h2>

                <p class="text-sm text-zinc-600 mb-4" x-show="confirm.action==='delete'">This action cannot be undone.</p>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="confirm.show=false"
                            class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600">
                        Cancel
                    </button>
                    <button type="button"
                            @click="
                                if (confirm.action==='edit') { $wire.startEdit(confirm.id) } else { $wire.delete(confirm.id) }
                                confirm.show=false
                            "
                            class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white">
                        Yes
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
