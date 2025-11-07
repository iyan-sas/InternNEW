{{-- resources/views/livewire/admin/school-year-settings.blade.php --}}
<div
    x-data
    x-init="
        // When this component fires a browser event...
        window.addEventListener('sy-changed', () => {
            // ...ping other tabs on the same origin
            try { localStorage.setItem('sy-changed', String(Date.now())) } catch(e) {}
        });
    "
>
    <style>[x-cloak]{display:none!important}</style>

    @if($compact)
        {{-- ✅ Compact toolbar (inline use inside admin show page) --}}
        <div class="flex flex-wrap items-center gap-2" x-data="{ addOpen:false }" x-cloak>
            {{-- Label --}}
            <label class="text-xs font-medium text-slate-700">School Year</label>

            {{-- Dropdown --}}
            <select wire:model="sy_id"
                class="rounded-md border border-slate-300 bg-white px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">— select —</option>
                @foreach($options as $opt)
                    <option value="{{ $opt['id'] }}">
                        {{ $opt['label'] }}{{ $opt['is_active'] ? ' • active' : '' }}
                    </option>
                @endforeach
            </select>

            {{-- Set Active (opens confirm modal) --}}
            <button type="button"
                wire:click="askSetActive"
                class="rounded-md bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
                wire:loading.attr="disabled">
                <span wire:loading.remove>Set Active</span>
                <span wire:loading>...</span>
            </button>

            {{-- Add toggle --}}
            <button type="button"
                class="rounded-md border border-slate-300 px-2.5 py-1.5 text-xs font-medium hover:bg-slate-100"
                @click="addOpen = !addOpen">
                + Add
            </button>

            {{-- Add field --}}
            <div x-show="addOpen" x-transition @click.outside="addOpen=false" class="flex items-center gap-2">
                <input type="text"
                    placeholder="YYYY-YYYY"
                    wire:model.defer="new_sy"
                    class="w-32 rounded-md border border-slate-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="button"
                    wire:click="add"
                    class="rounded-md bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500"
                    wire:loading.attr="disabled"
                    @click="addOpen=false">
                    <span wire:loading.remove>Save</span>
                    <span wire:loading>...</span>
                </button>
            </div>

            {{-- Flash Message --}}
            @if (session('success'))
                <span class="text-xs text-green-700 font-medium ml-2">
                    ✅ {{ session('success') }}
                </span>
            @endif

            {{-- Validation Error --}}
            @error('new_sy')
                <span class="text-xs text-red-600 ml-2">{{ $message }}</span>
            @enderror
        </div>

    @else
        {{-- ✅ Full-page version (standalone settings page) --}}
        <div class="space-y-5" x-data x-cloak>
            <h2 class="text-lg font-semibold text-slate-800">School Year Settings</h2>

            <div class="flex items-center gap-3">
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-slate-600">Current School Year</label>
                    <select wire:model="sy_id"
                        class="rounded-md border border-slate-300 bg-white px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— select —</option>
                        @foreach($options as $opt)
                            <option value="{{ $opt['id'] }}">
                                {{ $opt['label'] }}{{ $opt['is_active'] ? ' • active' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    {{-- Set Active (opens confirm modal) --}}
                    <button type="button"
                        wire:click="askSetActive"
                        class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove>Set Active</span>
                        <span wire:loading>...</span>
                    </button>

                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-slate-600">Add New</label>
                        <div class="flex gap-2">
                            <input type="text"
                                placeholder="YYYY-YYYY"
                                wire:model.defer="new_sy"
                                class="w-32 rounded-md border border-slate-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="button"
                                wire:click="add"
                                class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove>Save</span>
                                <span wire:loading>...</span>
                            </button>
                        </div>
                        @error('new_sy') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="text-sm text-green-700 font-medium">
                    ✅ {{ session('success') }}
                </div>
            @endif
        </div>
    @endif

    {{-- Confirmation Modal --}}
@if($confirming)
<template x-teleport="body">
    <div x-data x-init="setTimeout(()=>{},0)"
         class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-[420px] max-w-[92vw] mx-4">
            <h3 class="text-lg font-semibold text-zinc-900 mb-2">
                Set this School Year as active?
            </h3>
            <p class="text-sm text-zinc-600 mb-4">
                This will update the active School Year for everyone. Continue?
            </p>
            <div class="flex justify-end gap-3">
                <button type="button"
                        wire:click="cancelConfirm"
                        class="px-4 py-2 rounded-lg bg-zinc-100 text-zinc-800 hover:bg-zinc-200">
                    Cancel
                </button>
                <button type="button"
                        wire:click="proceedSetActive"
                        class="px-4 py-2 rounded-lg bg-blue-500 hover:bg-blue-500 text-white">
                    Yes, set active
                </button>
            </div>
        </div>
    </div>
</template>
@endif

</div>
