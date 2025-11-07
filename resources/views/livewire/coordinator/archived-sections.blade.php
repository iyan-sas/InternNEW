{{-- resources/views/livewire/coordinator/archived-sections.blade.php --}}
<div class="space-y-4">

    @php
        // pastel palettes for cards (cycled)
        $bgColors = [
            ['bg' => '#DBEAFE', 'header' => '#BFDBFE', 'border' => 'border-blue-400'],
            ['bg' => '#BBF7D0', 'header' => '#86EFAC', 'border' => 'border-green-400'],
            ['bg' => '#FEF9C3', 'header' => '#FDE68A', 'border' => 'border-yellow-400'],
            ['bg' => '#FBCFE8', 'header' => '#F9A8D4', 'border' => 'border-pink-400'],
            ['bg' => '#E9D5FF', 'header' => '#D8B4FE', 'border' => 'border-purple-400'],
            ['bg' => '#FECACA', 'header' => '#FCA5A5', 'border' => 'border-red-400'],
        ];

        $campusLabel  = $stream->campus ?? $stream->campus_name ?? 'Campus';
        $collegeLabel = $stream->college ?? $stream->college_name ?? 'College';
    @endphp

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <header class="px-4 sm:px-5 md:px-6 py-3 sm:py-4 border-b bg-slate-50/60 rounded-t-2xl">
            <h1 class="text-base sm:text-lg md:text-xl font-semibold text-slate-900 leading-tight">
                Archived Sections
            </h1>
            <p class="text-xs sm:text-sm md:text-base text-slate-600 mt-1 leading-relaxed">
                Previous school years for:
                <span class="font-medium">{{ $campusLabel }} — {{ $collegeLabel }}</span>
            </p>
        </header>

        <div class="p-3 sm:p-5 md:p-6">
            @if ($grouped->isEmpty())
                <p class="text-sm sm:text-base text-slate-500">No archived sections yet.</p>
            @else
                <div class="space-y-3 sm:space-y-4">
                    @foreach ($grouped as $sy => $sections)
                        @php
                            $prettySy = str_replace('-', '–', (string) $sy);
                            $count    = $sections->count();
                        @endphp

                        <div x-data="{ open: false }" class="rounded-xl border border-slate-200 overflow-hidden bg-white">
                            {{-- Group Header --}}
                            <button
                                type="button"
                                @click="open = !open"
                                :aria-expanded="open"
                                class="w-full px-3 py-3 sm:px-4 sm:py-3 flex items-center justify-between gap-3 bg-slate-50 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 transition"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <svg :class="open ? 'rotate-90' : ''"
                                         class="h-5 w-5 text-slate-600 transition-transform shrink-0"
                                         viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 0 1 0-1.414L10.586 10 7.293 6.707a1 1 0 1 1 1.414-1.414l4 4a1 1 0 0 1 0 1.414l-4 4a1 1 0 0 1-1.414 0Z" clip-rule="evenodd"/>
                                    </svg>

                                    <span class="text-sm sm:text-base font-semibold text-slate-900 truncate">
                                        School Year {{ $prettySy }}
                                    </span>
                                </div>

                                <span class="text-[11px] sm:text-xs font-medium px-2.5 py-1 rounded-full bg-slate-200 text-slate-700 shrink-0">
                                    {{ $count }} {{ \Illuminate\Support\Str::plural('section', $count) }}
                                </span>
                            </button>

                            {{-- Group Body --}}
                            <div x-show="open" x-collapse x-cloak class="p-3 sm:p-4 border-t border-slate-200">
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                                    @foreach ($sections->values() as $i => $s)
                                        @php
                                            $colors  = $bgColors[$i % count($bgColors)];
                                            // Keep your existing route signature
                                            $openUrl = route('section.show', ['stream' => $stream->id, 'section' => $s->id]);
                                            $syPill  = $s->school_year ? str_replace('-', '–', $s->school_year) : '—';
                                        @endphp

                                        <a href="{{ $openUrl }}"
                                           class="group rounded-lg border {{ $colors['border'] }} bg-white shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                           style="background-color: {{ $colors['bg'] }};">
                                            {{-- Card Header --}}
                                            <div class="px-3 sm:px-4 py-2 rounded-t-lg border-b border-white/40">
                                                <div class="w-full min-w-0">
                                                    <div class="flex items-center justify-between min-w-0">
                                                        <span class="font-semibold text-sm sm:text-base truncate" title="{{ $s->section_name }}">
                                                            {{ $s->section_name }}
                                                        </span>
                                                        <span class="text-[10px] sm:text-[11px] md:text-xs font-medium px-2 py-0.5 rounded-full bg-white/70 border border-slate-200 text-slate-700 shrink-0">
                                                            SY {{ $syPill }}
                                                        </span>
                                                    </div>
                                                    <p class="mt-0.5 text-xs sm:text-sm text-slate-800 truncate">
                                                        Coordinator:
                                                        <span class="font-semibold">
                                                            {{ $s->coordinator?->name ?? $s->coordinator_name ?? '—' }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>

                                            {{-- Card Body --}}
                                            <div class="px-3 sm:px-4 py-2">
                                                <div class="flex items-center justify-between">
                                                    <p class="text-xs sm:text-sm text-slate-700">
                                                        Tap to view section details.
                                                    </p>
                                                    {{-- View-only badge --}}
                                                    <span class="ml-2 text-[10px] sm:text-xs font-medium px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 border border-amber-200">
                                                        Archived • View-only
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
