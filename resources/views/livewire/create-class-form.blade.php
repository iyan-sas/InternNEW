<div>
    <style>
        [x-cloak]{display:none!important}
        /* Main Create Class modal (your style) */
        [data-create-class-modal]{
            border-radius:20px!important; overflow:hidden!important;
            border:1px solid rgba(15,23,42,.12);
            box-shadow:0 18px 48px rgba(0,0,0,.18),0 6px 20px rgba(0,0,0,.10);
            background:#fff; width:680px; max-width:92vw;
        }
        [data-create-class-modal] select,[data-create-class-modal] button,[data-create-class-modal] input{
            border-radius:12px;
        }
        .inline-card{
            border:1px solid rgba(15,23,42,.12);
            border-radius:14px;
            padding:12px;
            background:#fafafa;
        }
    </style>

    <!-- Open Button -->
    <button type="button" wire:click="open"
            class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">
        + Create Class
    </button>

    @if ($showModal)
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/40 z-40"></div>

        <!-- Single Modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div data-create-class-modal role="dialog" aria-modal="true" class="mx-auto">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-semibold">Create Class</h2>
                </div>

                <div class="p-6">
                    @if (session('success'))
                        <div class="mb-3 text-green-700 text-sm">{{ session('success') }}</div>
                    @endif

                    <form wire:submit.prevent="create" class="space-y-5">
                        {{-- CAMPUS --}}
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-800">Campus</label>
                            <select wire:model.live="campus_id"
                                    class="w-full h-11 border border-gray-300 px-3 text-base
                                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Campus</option>
                                @foreach ($campusOptions as $c)
                                    <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                                @endforeach
                            </select>
                            @error('campus_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror

                            <!-- Inline add campus -->
                            <div class="mt-2">
                                @if(!$showAddCampus)
                                    <button type="button"
                                            wire:click="$set('showAddCampus', true)"
                                            class="text-sm text-blue-600 hover:text-blue-700">
                                        + Add new campus
                                    </button>
                                @else
                                    <div class="inline-card">
                                        <div class="grid gap-2">
                                            <label class="text-sm text-gray-700">New campus name</label>
                                            <input type="text" wire:model.defer="newCampusName"
                                                   class="w-full border px-3 py-2"
                                                   placeholder="e.g., Bacolor Campus">
                                            @error('newCampusName') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
                                            <div class="flex justify-end gap-2 pt-1">
                                                <button type="button" class="px-3 py-1.5 rounded-lg border"
                                                        wire:click="$set('showAddCampus', false)">Cancel</button>
                                                <button type="button" class="px-3 py-1.5 rounded-lg bg-blue-600 text-white"
                                                        wire:click="saveNewCampus" wire:loading.attr="disabled">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- COLLEGE --}}
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-800">College</label>
                            <select wire:model.live="college_id"
                                    class="w-full h-11 border border-gray-300 px-3 text-base
                                           disabled:bg-gray-100 disabled:text-gray-500
                                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    @disabled(!$campus_id)>
                                <option value="">{{ $campus_id ? 'Select College' : 'Select Campus first' }}</option>
                                @foreach ($collegeOptions as $c)
                                    <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                                @endforeach
                            </select>
                            @error('college_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror

                            <!-- Inline add college -->
                            <div class="mt-2">
                                @if(!$showAddCollege)
                                    <button type="button"
                                            wire:click="openAddCollege"
                                            class="text-sm text-blue-600 hover:text-blue-700"
                                            @disabled(!$campus_id)">
                                        + Add new college
                                    </button>
                                @else
                                    <div class="inline-card">
                                        <div class="grid gap-2">
                                            <div class="grid gap-1">
                                                <label class="text-sm text-gray-700">Campus</label>
                                                <select wire:model.live="addCollegeCampusId"
                                                        class="w-full border px-3 py-2">
                                                    <option value="">Select Campus</option>
                                                    @foreach($campusOptions as $c)
                                                        <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                                                    @endforeach
                                                </select>
                                                @error('addCollegeCampusId') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
                                            </div>

                                            <div class="grid gap-1">
                                                <label class="text-sm text-gray-700">New college name</label>
                                                <input type="text" wire:model.defer="newCollegeName"
                                                       class="w-full border px-3 py-2">
                                                @error('newCollegeName') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
                                            </div>

                                            <div class="flex justify-end gap-2 pt-1">
                                                <button type="button" class="px-3 py-1.5 rounded-lg border"
                                                        wire:click="$set('showAddCollege', false)">Cancel</button>
                                                <button type="button" class="px-3 py-1.5 rounded-lg bg-blue-600 text-white"
                                                        wire:click="saveNewCollege" wire:loading.attr="disabled">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- (Optional) DEPARTMENT – uncomment if you’ll use departments now --}}
                        {{--
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-800">Department</label>
                            <select wire:model.live="department_id"
                                    class="w-full h-11 border border-gray-300 px-3 text-base
                                           disabled:bg-gray-100 disabled:text-gray-500
                                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    @disabled(!$college_id)>
                                <option value="">{{ $college_id ? 'Select Department' : 'Select College first' }}</option>
                                @foreach ($deptOptions as $d)
                                    <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                                @endforeach
                            </select>
                            @error('department_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror

                            <div class="mt-2">
                                @if(!$showAddDept)
                                    <button type="button"
                                            wire:click="openAddDept"
                                            class="text-sm text-blue-600 hover:text-blue-700"
                                            @disabled(!$campus_id)">
                                        + Add new department
                                    </button>
                                @else
                                    <div class="inline-card">
                                        <div class="grid gap-2">
                                            <div>
                                                <label class="text-sm text-gray-700">Campus</label>
                                                <select wire:model.live="addDeptCampusId" class="w-full border px-3 py-2">
                                                    <option value="">Select Campus</option>
                                                    @foreach($campusOptions as $c)
                                                        <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                                                    @endforeach
                                                </select>
                                                @error('addDeptCampusId') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
                                            </div>

                                            <div>
                                                <label class="text-sm text-gray-700">College</label>
                                                <select wire:model.live="addDeptCollegeId"
                                                        class="w-full border px-3 py-2" @disabled(!$addDeptCampusId)>
                                                    <option value="">{{ $addDeptCampusId ? 'Select College' : 'Select Campus first' }}</option>
                                                    @foreach($addDeptCollegeOptions as $c)
                                                        <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                                                    @endforeach
                                                </select>
                                                @error('addDeptCollegeId') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
                                            </div>

                                            <div>
                                                <label class="text-sm text-gray-700">New department name</label>
                                                <input type="text" wire:model.defer="newDeptName"
                                                       class="w-full border px-3 py-2">
                                                @error('newDeptName') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
                                            </div>

                                            <div class="flex justify-end gap-2 pt-1">
                                                <button type="button" class="px-3 py-1.5 rounded-lg border"
                                                        wire:click="$set('showAddDept', false)">Cancel</button>
                                                <button type="button" class="px-3 py-1.5 rounded-lg bg-blue-600 text-white"
                                                        wire:click="saveNewDept" wire:loading.attr="disabled">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        --}}

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 pt-1">
                            <button type="button" wire:click="cancel"
                                    class="px-5 py-2.5 bg-red-500 text-white hover:bg-red-600">Cancel</button>
                            <button type="submit"
                                    class="px-5 py-2.5 bg-blue-600 text-white hover:bg-blue-700">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
