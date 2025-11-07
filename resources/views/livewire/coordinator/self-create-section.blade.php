{{-- resources/views/livewire/coordinator/self-create-section.blade.php --}}
<div class="mx-auto max-w-5xl space-y-6 px-3 sm:px-0">
    {{-- ===================== Top card: Links ===================== --}}
    <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <header class="flex items-center justify-between gap-3 rounded-t-xl border-b bg-slate-50/60 px-4 sm:px-5 py-3 sm:py-4">
            <h3 class="text-sm sm:text-base font-semibold text-slate-900">
                Create Class (Coordinator &amp; Section)
            </h3>

            <button
                type="button"
                onclick="document.getElementById('createFormAnchor')?.scrollIntoView({behavior:'smooth'})"
                class="shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                + Create
            </button>
        </header>

        <div class="space-y-4 p-4 sm:p-5">
            {{-- ---------- Coordinator self-create link (this page) ---------- --}}
            @php $selfCreateUrl = $selfCreateUrl ?? ''; @endphp

            <div class="rounded-lg border border-slate-200 bg-white p-3 sm:p-4">
                <div class="mb-2 text-sm font-medium text-slate-800">Coordinator Self-Create Link</div>
                <p class="mb-3 text-xs sm:text-sm text-slate-600">
                    Share this link to let another coordinator open this create-section page.
                </p>

                <div class="relative">
                    <input
                        type="text"
                        readonly
                        value="{{ $selfCreateUrl }}"
                        class="w-full overflow-x-auto whitespace-nowrap rounded-lg border border-slate-300 bg-slate-50 px-3 pr-28 py-2 text-xs sm:text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        aria-label="Coordinator self-create link"
                    />
                    <div class="pointer-events-auto absolute inset-y-0 right-2 hidden items-center gap-1.5 sm:flex">
                        <button
                            type="button"
                            onclick="navigator.clipboard.writeText('{{ $selfCreateUrl }}'); this.textContent='Copied'; setTimeout(()=>this.textContent='Copy',1400)"
                            class="rounded-md bg-blue-600 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-blue-700"
                            @if(!$selfCreateUrl) disabled @endif
                        >Copy</button>
                        <a
                            href="{{ $selfCreateUrl }}"
                            target="_blank" rel="noopener"
                            class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                            @if(!$selfCreateUrl) aria-disabled="true" tabindex="-1" @endif
                        >Open</a>
                    </div>
                </div>

                <div class="mt-2 grid grid-cols-2 gap-2 sm:hidden">
                    <button
                        type="button"
                        onclick="navigator.clipboard.writeText('{{ $selfCreateUrl }}'); this.textContent='Copied'; setTimeout(()=>this.textContent='Copy',1400)"
                        class="w-full rounded-md bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700 disabled:opacity-60"
                        @if(!$selfCreateUrl) disabled @endif
                    >Copy</button>
                    <a
                        href="{{ $selfCreateUrl }}"
                        target="_blank" rel="noopener"
                        class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-center text-xs font-semibold text-slate-700 hover:bg-slate-50"
                        @if(!$selfCreateUrl) aria-disabled="true" tabindex="-1" @endif
                    >Open</a>
                </div>

                <details class="group mt-2">
                    <summary class="cursor-pointer select-none text-xs text-blue-700 hover:underline">Show full link</summary>
                    <div
                        class="mt-2 rounded-lg border border-slate-200 bg-slate-50 p-2 text-[11px] sm:text-xs text-slate-700 break-words"
                        style="word-break: break-all;"
                    >{{ $selfCreateUrl }}</div>
                </details>
            </div>
        </div>
    </section>

    {{-- ===================== Create Section form ===================== --}}
    <section id="createFormAnchor" class="flex items-center justify-center py-1">
        <div class="w-full max-w-xl overflow-hidden rounded-2xl border bg-white shadow-2xl">
            <header class="border-b px-5 sm:px-6 py-3">
                <h2 class="text-base sm:text-lg font-semibold">Create Section</h2>
            </header>

            <div class="p-5 sm:p-6">
                {{-- Flash messages --}}
                @if (session('message'))
                    <div class="mb-3 rounded border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">
                        {{ session('message') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-3 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                <form wire:submit.prevent="create" class="space-y-5">
                    {{-- Section Name --}}
                    <div>
                        <label for="section_name" class="mb-1 block text-sm font-medium">Section <span class="text-red-600">*</span></label>
                        <input
                            id="section_name"
                            type="text"
                            wire:model.defer="section_name"
                            class="h-11 w-full rounded-md border border-gray-300 px-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="e.g., BSIT 4F"
                            autofocus
                        />
                        @error('section_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- School Year (required; format YYYY-YYYY) --}}
                    <div x-data
                         x-init="$watch('school_year', v => {})">
                        <label for="school_year" class="mb-1 block text-sm font-medium">
                            School Year <span class="text-red-600">*</span>
                        </label>
                        <input
                            id="school_year"
                            type="text"
                            wire:model.defer="school_year"
                            inputmode="numeric"
                            pattern="^\d{4}[-–]\d{4}$"
                            maxlength="9"
                            class="h-11 w-full rounded-md border border-gray-300 px-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="e.g., 2026-2027"
                            oninput="this.value=this.value.replace(/[^\d\-–]/g,'').slice(0,9)"
                        />
                        <p class="mt-1 text-[11px] text-slate-500">Format: <strong>YYYY-YYYY</strong> (example: 2026-2027)</p>
                        @error('school_year')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Coordinator (auto; read-only) --}}
                    <div>
                        <div class="mb-1 block text-sm font-medium">Coordinator (auto)</div>
                        <div class="flex h-11 items-center rounded-md border bg-slate-50 px-3 text-sm">
                            {{ $currentCoordinator ?? '—' }}
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            Taken from the signed-in account and saved to the section.
                        </p>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-2">
                        <div class="grid grid-cols-1 gap-2 sm:flex sm:justify-end">
                            <button
                                type="button"
                                wire:click="cancel"
                                class="w-full rounded-md border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100 sm:w-auto"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="w-full rounded-md bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60 sm:w-auto"
                            >
                                <span wire:loading.remove>Create</span>
                                <span wire:loading>Creating…</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <style>
        .break-words{overflow-wrap:anywhere; hyphens:auto;}
    </style>
</div>
