{{-- resources/views/livewire/coordinator/sections-panel.blade.php --}}
@php
    $bgColors = [
        ['bg' => '#DBEAFE', 'header' => '#BFDBFE', 'border' => 'border-blue-400'],
        ['bg' => '#BBF7D0', 'header' => '#86EFAC', 'border' => 'border-green-400'],
        ['bg' => '#FEF9C3', 'header' => '#FDE68A', 'border' => 'border-yellow-400'],
        ['bg' => '#FBCFE8', 'header' => '#F9A8D4', 'border' => 'border-pink-400'],
        ['bg' => '#E9D5FF', 'header' => '#D8B4FE', 'border' => 'border-purple-400'],
        ['bg' => '#FECACA', 'header' => '#FCA5A5', 'border' => 'border-red-400'],
    ];

    $inviteUrl = !empty($stream->coordinator_invite_token ?? null)
        ? route('coordinator.join', ['token' => $stream->coordinator_invite_token])
        : null;
@endphp

<div x-data="{ confirm:{show:false,action:'',id:null}, copiedInvite:false }">
    <style>[x-cloak]{display:none!important}</style>

    @if (session('success'))
        <div class="mb-3 text-green-700 text-sm">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm mb-4">
        <div class="px-5 py-4 border-b bg-slate-50/60 rounded-t-xl flex items-center justify-between">
            <h3 class="font-semibold text-slate-900">(Coordinator &amp; Section)</h3>
        </div>

        <div class="p-4 md:p-5 space-y-4">
            {{-- Self-create invite link --}}
            <div class="rounded-lg border border-slate-200 p-4 bg-slate-50/50">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="min-w-[220px]">
                        <p class="text-sm font-medium text-slate-800">Coordinator Self-Create Link</p>
                        <p class="text-xs text-slate-500">Share this link to let the coordinator create the section themselves.</p>
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
                                <a href="{{ $inviteUrl }}" target="_blank"
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

            {{-- Sections --}}
            @if ($sections->isEmpty())
                <p class="text-sm text-slate-500">No sections yet.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach ($sections as $i => $s)
                        @php
                            $colors  = $bgColors[$i % count($bgColors)];
                            $openUrl = route('section.show', ['stream' => $stream->id, 'section' => $s->id]);
                        @endphp

                        <a href="{{ $openUrl }}"
                           class="block rounded-lg border-2 border-blue-500 shadow-sm ring-2 ring-blue-500/20 ring-offset-1 ring-offset-white transition-all duration-150 hover:ring-blue-600/30 {{ $colors['border'] }}"
                           style="background-color: {{ $colors['bg'] }};">
                            <div class="px-4 py-2 rounded-t-lg border-b border-blue-300/50" style="background-color: {{ $colors['header'] }};">
                                <div class="font-bold text-base sm:text-lg">{{ $s->section_name }}</div>
                                <div class="text-sm text-black/80">
                                    Coordinator: <span class="font-medium">{{ $s->coordinator?->name ?? $s->coordinator_name ?? '—' }}</span>
                                </div>
                            </div>
                            <div class="p-4 text-black">
                                <p class="text-sm text-gray-700">Click the section name above to open details.</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- CREATE MODAL --}}
    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 z-40"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" wire:key="create-modal">
            <div class="rounded-2xl border bg-white shadow-2xl mx-auto" role="dialog" aria-modal="true"
                 style="width:680px; max-width:92vw;">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold">Create Section</h2>
                </div>
                <div class="p-6">
                    <form wire:submit.prevent="create" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Section</label>
                            <input type="text" wire:model.defer="section_name"
                                   class="w-full h-11 rounded-md border-gray-300 px-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="e.g., BSIT 4F" autofocus>
                            @error('section_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Name of Coordinator (manual)</label>
                            <input type="text" wire:model.defer="coordinator_name"
                                   class="w-full h-11 rounded-md border-gray-300 px-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="e.g., Prof. Dela Cruz">
                            @error('coordinator_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" wire:click="cancel" class="px-5 py-2.5 bg-red-500 text-white rounded-md hover:bg-red-600">Cancel</button>
                            <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-md hover:bg-blue-700">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
