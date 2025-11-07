<div class="border rounded-md bg-white shadow-sm" x-data="{}">
    <!-- Header (click to expand/collapse) -->
    <button
        type="button"
        class="w-full flex items-center justify-between px-4 py-3"
        wire:click="toggle"
    >
        <div class="flex items-center gap-2">
            <h3 class="text-lg font-semibold text-gray-900">OJT Information</h3>

            @if(!$profile)
                <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">
                    Not yet started
                </span>
            @endif

            @if(session('ojt_saved'))
                <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-800">
                    Saved
                </span>
            @endif
        </div>

        <!-- caret -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 transform transition"
             :class="$wire.open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
        </svg>
    </button>

    <!-- Collapsible body -->
    <div x-cloak class="px-4 pb-4">
        @if($open)
            @if($editing)
                {{-- === EDIT / CREATE FORM === --}}
                @php
                    // Guards: keep the view safe even if component forgot to init arrays.
                    $regionsSafe    = $regions           ?? ($this->regions           ?? []);
                    $provincesSafe  = $company_provinces ?? ($this->company_provinces ?? []);
                    $citiesSafe     = $company_cities    ?? ($this->company_cities    ?? []);
                    $barangaysSafe  = $company_barangays ?? ($this->company_barangays ?? []);
                @endphp

                @if(empty($regionsSafe))
                    <div class="mb-4 rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-amber-800 text-sm">
                        Region list is empty. Make sure PSGC data is seeded, then refresh.
                    </div>
                @endif

                <div class="space-y-6" wire:key="ojt-form">
                    {{-- Basic info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Full Name</label>
                            <input type="text" class="w-full border rounded px-3 py-2"
                                   wire:model.defer="name">
                            @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Contact Number <span class="text-red-500">*</span></label>
                            <input type="text" class="w-full border rounded px-3 py-2"
                                   wire:model.defer="contact" placeholder="09xxxxxxxxx">
                            @error('contact') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Home Address</label>
                            <input type="text" class="w-full border rounded px-3 py-2"
                                   wire:model.defer="address" placeholder="House/Unit, Street, Barangay, City">
                            @error('address') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Company / HTE --}}
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Company / HTE Name <span class="text-red-500">*</span></label>
                            <input type="text" class="w-full border rounded px-3 py-2"
                                   wire:model.defer="company_name" placeholder="e.g., ABC Tech Corp.">
                            @error('company_name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Region --}}
                            <div>
                                <label class="block text-sm mb-1">Region <span class="text-red-500">*</span></label>
                                <select class="w-full border rounded px-3 py-2"
                                        wire:model.live.debounce.200ms="company_region"
                                        wire:loading.attr="disabled">
                                    <option value="">Select Region</option>
                                    @foreach ($regionsSafe as $r)
                                        @php
                                            $code = is_array($r) ? ($r['code'] ?? null) : ($r->code ?? null);
                                            $name = is_array($r) ? ($r['name'] ?? null) : ($r->name ?? null);
                                        @endphp
                                        @if($code && $name)
                                            <option value="{{ $code }}">{{ $name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('company_region') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Province (may be empty for NCR/independent cities) --}}
                            <div>
                                <label class="block text-sm mb-1">Province</label>
                                <select class="w-full border rounded px-3 py-2"
                                        wire:model.live.debounce.200ms="company_province"
                                        @disabled(empty($provincesSafe)) wire:loading.attr="disabled">
                                    <option value="">
                                        @if(empty($provincesSafe)) No province (NCR / Independent)
                                        @else Select Province @endif
                                    </option>
                                    @foreach ($provincesSafe as $p)
                                        @php
                                            $code = is_array($p) ? ($p['code'] ?? null) : ($p->code ?? null);
                                            $name = is_array($p) ? ($p['name'] ?? null) : ($p->name ?? null);
                                        @endphp
                                        @if($code && $name)
                                            <option value="{{ $code }}">{{ $name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('company_province') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- City / Municipality --}}
                            <div>
                                <label class="block text-sm mb-1">City / Municipality <span class="text-red-500">*</span></label>
                                <select class="w-full border rounded px-3 py-2"
                                        wire:model.live.debounce.200ms="company_city"
                                        @disabled(empty($citiesSafe)) wire:loading.attr="disabled">
                                    <option value="">Select City/Municipality</option>
                                    @foreach ($citiesSafe as $c)
                                        @php
                                            $code = is_array($c) ? ($c['code'] ?? null) : ($c->code ?? null);
                                            $name = is_array($c) ? ($c['name'] ?? null) : ($c->name ?? null);
                                        @endphp
                                        @if($code && $name)
                                            <option value="{{ $code }}">{{ $name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('company_city') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Barangay --}}
                            <div>
                                <label class="block text-sm mb-1">Barangay <span class="text-red-500">*</span></label>
                                <select class="w-full border rounded px-3 py-2"
                                        wire:model.live.debounce.200ms="company_brgy"
                                        @disabled(empty($barangaysSafe)) wire:loading.attr="disabled">
                                    <option value="">Select Barangay</option>
                                    @foreach ($barangaysSafe as $b)
                                        @php
                                            $code = is_array($b) ? ($b['code'] ?? null) : ($b->code ?? null);
                                            $name = is_array($b) ? ($b['name'] ?? null) : ($b->name ?? null);
                                        @endphp
                                        @if($code && $name)
                                            <option value="{{ $code }}">{{ $name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('company_brgy') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Free-text detail + ZIP --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm mb-1">Detail Address</label>
                                <input type="text" class="w-full border rounded px-3 py-2"
                                       wire:model.defer="company_detail"
                                       placeholder="Building/Unit, Street, Landmark">
                                @error('company_detail') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm mb-1">ZIP</label>
                                <input type="text" class="w-full border rounded px-3 py-2"
                                       wire:model.defer="company_zip">
                                @error('company_zip') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" class="px-4 py-2 rounded border" wire:click="cancel">Cancel</button>
                        <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" 
                                wire:click="save" 
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed">
                            <span wire:loading.remove>Save OJT Info</span>
                            <span wire:loading>Saving...</span>
                        </button>
                    </div>
                </div>
            @else
                {{-- === SUMMARY VIEW (read-only) === --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Full Name</p>
                        <p class="font-medium">{{ $name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Contact Number</p>
                        <p class="font-medium">{{ $contact }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500">Home Address</p>
                        <p class="font-medium break-words">{{ $address }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Company / HTE Name</p>
                        <p class="font-medium break-words">{{ $company_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Company Address</p>
                        <p class="font-medium break-words">{{ $company_address ?: $company_detail }}</p>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <p class="text-xs text-gray-500">Last updated: {{ $profile?->updated_at?->diffForHumans() }}</p>
                    <button type="button" class="px-3 py-2 rounded border hover:bg-gray-50" wire:click="edit">Edit</button>
                </div>
            @endif
        @else
            {{-- collapsed; show a tiny teaser + CTA when no profile --}}
            @if(!$profile)
                <div class="pb-4">
                    <p class="text-sm text-blue-500">
                        Click "OJT Information" to start the form once you begin your OJT.
                    </p>
                    <button type="button" class="mt-2 text-sm px-3 py-1.5 bg-blue-600 text-white rounded"
                            wire:click="startForm">
                        Start OJT Form
                    </button>
                </div>
            @endif
        @endif
    </div>
</div>