@php
    $campusLabel     = $stream->campus ?? $stream->campus_name ?? 'Campus';
    $collegeLabel    = $stream->college ?? $stream->college_name ?? 'College';
    $departmentLabel = $stream->department ?? $stream->department_name ?? null;

    // Robust admin check (works for string role or backed enum)
    $roleRaw  = auth()->user()?->role;
    $roleStr  = is_object($roleRaw) && property_exists($roleRaw, 'value')
                ? strtolower($roleRaw->value)
                : strtolower((string) $roleRaw);
    $isAdmin  = ($roleStr === 'admin');
@endphp

<x-layouts.app :title="$campusLabel . ' - ' . $collegeLabel">
    {{-- =======================
         Header / Shell
    ======================== --}}
    <section aria-labelledby="page-title"
        class="rounded-xl border border-slate-200 bg-white shadow-sm mb-4 w-full max-w-full">
        <header class="px-3 sm:px-5 md:px-6 py-3 sm:py-4 border-b bg-slate-50/60 rounded-t-xl">
            <div class="flex items-start sm:items-center justify-between gap-3 flex-wrap">
                <div>
                    <h1 id="page-title"
                        class="text-base sm:text-lg md:text-xl font-semibold text-slate-900 break-words hyphens-auto">
                        {{ $campusLabel }} — {{ $collegeLabel }}
                    </h1>

                    @if($departmentLabel)
                        <p class="text-xs sm:text-sm md:text-base text-slate-600 mt-0.5 break-words hyphens-auto">
                            Department: <span class="font-medium">{{ $departmentLabel }}</span>
                        </p>
                    @endif

                    <p class="text-xs sm:text-sm md:text-base text-slate-500 mt-1 break-words hyphens-auto">
                        Manage Sections (Coordinator &amp; Section) for this Campus/Department.
                    </p>
                </div>

                {{-- Admin-only: School Year toolbar (compact) --}}
                @if($isAdmin)
                    {{-- Try SchoolYearSettings first; fallback to SchoolYearInline if that’s what you created --}}
                    @if(View::exists('livewire.admin.school-year-settings'))
                        <livewire:admin.school-year-settings :compact="true" />
                    @elseif(View::exists('livewire.admin.school-year-inline'))
                        <livewire:admin.school-year-inline />
                    @else
                        {{-- Helpful hint if the component view isn’t found --}}
                        <div class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-3 py-2">
                            School Year component not found.
                            Create it via:
                            <code>php artisan make:livewire Admin/SchoolYearSettings</code>
                        </div>
                    @endif
                @endif
            </div>
        </header>

        {{-- =======================
             Body
        ======================== --}}
        <div class="p-3 sm:p-4 md:p-5 overflow-x-auto">
            <div class="w-full max-w-full space-y-4 sections-panel">
                <livewire:coordinator.sections-panel
                    :stream="$stream"
                    :canCreate="false"
                    :canManage="false"
                />
            </div>
        </div>
    </section>

    {{-- =======================
         Scoped helpers for the Livewire panel
         (kept minimal and mobile-first)
    ======================== --}}
    <style>
        .sections-panel input[type="text"],
        .sections-panel input[type="url"],
        .sections-panel select,
        .sections-panel textarea { width: 100%; max-width: 100%; }

        .sections-panel .button-group,
        .sections-panel .btn-group,
        .sections-panel .actions { display:flex; flex-wrap:wrap; gap:.5rem; }

        .sections-panel .button-group > *,
        .sections-panel .btn-group > *,
        .sections-panel .actions > * {
            min-height: 2.75rem; padding-left: 1rem; padding-right: 1rem;
        }

        .sections-panel .card,
        .sections-panel .cell,
        .sections-panel .truncate-safe,
        .sections-panel .wrap { overflow-wrap:break-word; word-wrap:break-word; hyphens:auto; }

        .sections-panel .card-pad { padding:.75rem; }
        @media (min-width: 640px) { .sections-panel .card-pad { padding:1rem; } }

        @media (min-width: 640px) {
            .sections-panel .button-group,
            .sections-panel .btn-group,
            .sections-panel .actions { flex-wrap:nowrap; }
        }

        .sections-panel a:focus-visible,
        .sections-panel button:focus-visible{
            outline:2px solid #3b82f6; outline-offset:2px; border-radius:.5rem;
        }
    </style>
</x-layouts.app>
