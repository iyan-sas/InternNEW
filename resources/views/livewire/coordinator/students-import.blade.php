<div class="space-y-4" x-data="{}">
    {{-- Flash message --}}
    @if (session('message'))
        <div class="rounded border border-green-400 bg-green-50 text-green-600 px-3 py-2">
            {{ session('message') }}
        </div>
    @endif

    {{-- Upload card --}}
    <div class="rounded-lg border bg-white p-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <h3 class="font-semibold">Import Students from Excel</h3>

                @error('rosterFile')
                    <div class="mt-2 rounded border border-red-300 bg-red-50 text-red-600 px-3 py-2 text-sm">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="flex w-full flex-col items-stretch gap-3 sm:w-auto sm:flex-row sm:items-center">
                <input
                    type="file"
                    wire:model="rosterFile"
                    class="block w-full sm:w-64 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-blue-700"
                    accept=".xlsx,.xls,.csv"
                    aria-label="Choose Excel file to import"
                >

                <div class="text-sm text-gray-600" wire:loading wire:target="rosterFile">
                    Uploading… please wait
                </div>

                <button
                    type="button"
                    class="rounded-lg bg-blue-600 text-white px-4 py-2 hover:bg-blue-700 disabled:opacity-50"
                    wire:click="import"
                    wire:loading.attr="disabled"
                    wire:target="import, rosterFile"
                    x-bind:disabled="!$wire.rosterFile"
                >
                    <span wire:loading.remove wire:target="import">Import</span>
                    <span wire:loading wire:target="import">Importing…</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Manual add card --}}
    <div class="rounded-lg border bg-white p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Student No</label>
                <input
                    type="text"
                    wire:model.defer="manualStudentNo"
                    class="w-full rounded border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="e.g. 2022311998">
                @error('manualStudentNo')
                    <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Full Name</label>
                <input
                    type="text"
                    wire:model.defer="manualFullName"
                    class="w-full rounded border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="e.g. Juan Dela Cruz">
                @error('manualFullName')
                    <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-end">
                <button
                    type="button"
                    class="w-full sm:w-auto rounded-lg bg-emerald-500 text-white px-4 py-2 text-sm hover:bg-emerald-500 disabled:opacity-50"
                    wire:click="addManual"
                    wire:loading.attr="disabled"
                    wire:target="addManual">
                    <span wire:loading.remove wire:target="addManual">Add Student</span>
                    <span wire:loading wire:target="addManual">Adding…</span>
                </button>
            </div>
        </div>

        <p class="mt-2 text-xs text-gray-500">
            Tip: This is for irregular/late enrollees. Duplicates (same Student No within this section) will be updated, not duplicated.
        </p>
    </div>

    {{-- Roster: responsive GRID of cards (1 → 2 → 3 → 4 columns) --}}
    <div class="rounded-lg border bg-white">
        <div class="px-4 py-3 font-semibold">Students (Roster)</div>

        @if ($roster->isEmpty())
            <div class="px-4 pb-5 text-sm text-gray-600">
                No students listed yet. Import an Excel file to populate this list.
            </div>
        @else
            <div class="px-4 pb-4">
                <!-- Change the breakpoints to your liking:
                     grid-cols-1 xs, sm:grid-cols-2, lg:grid-cols-3, xl:grid-cols-4 -->
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($roster as $s)
                        <div class="rounded-lg border border-gray-200 p-3 hover:border-blue-300 transition">
                            <div class="text-xs text-gray-500">Student No</div>
                            <div class="font-medium truncate">{{ $s->student_no }}</div>

                            <div class="mt-2 text-xs text-gray-500">Full Name</div>
                            <span class="break-words text-gray-900 font-medium" title="{{ $s->full_name }}">
                                {{ $s->full_name }}
                            </span>

                            <div class="mt-2 text-xs text-gray-500">Section</div>
                            <div class="whitespace-nowrap">{{ $section->section_name ?? '—' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
