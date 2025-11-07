<div class="mx-auto max-w-7xl space-y-4">

    @if (session('message'))
        <div class="bg-green-50 border border-green-400 text-green-700 p-2 rounded">
            {{ session('message') }}
        </div>
    @endif

    {{-- Pending --}}
    <section class="bg-white border rounded">
        <div class="px-4 py-3 font-semibold text-lg flex items-center justify-between">
            <span>Pending Students</span>
            <span class="text-xs text-gray-500">{{ $pending->count() }} total</span>
        </div>

        @if ($pending->isEmpty())
            <div class="px-4 py-3 text-sm text-gray-500">No pending submissions.</div>
        @else
            {{-- 1 col (mobile), 2 cols (sm), 3 cols (lg+) --}}
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($pending as $v)
                    @php
                        $hasCor = (bool) $v->cor_file;
                        $hasId  = (bool) $v->id_file;

                        // Robust section/coordinator access
                        $sectionName = $v->section->name
                            ?? $v->section->section_name
                            ?? null;
                        $coordName   = $v->section->coordinator->name
                            ?? null;
                    @endphp

                    <article class="h-full rounded-lg border bg-white p-4 flex flex-col justify-between">
                        <div>
                            <div class="font-medium">
                                {{ $v->user?->name ?? 'Unknown User' }}
                                <span class="text-xs text-gray-500">({{ $v->user?->email ?? '—' }})</span>
                            </div>

                            {{-- NEW: Section & Coordinator --}}
                            @if ($sectionName || $coordName)
                                <div class="mt-1 text-sm text-gray-700 leading-5">
                                    @if ($sectionName)
                                        <div><span class="font-medium">Section:</span> {{ $sectionName }}</div>
                                    @endif
                                    @if ($coordName)
                                        <div><span class="font-medium">Coordinator:</span> {{ $coordName }}</div>
                                    @endif
                                </div>
                            @endif

                            {{-- FILE LINKS --}}
                            <div class="mt-2 text-sm inline-flex flex-wrap items-center gap-4">
                                @if ($hasCor)
                                    <a href="{{ route('verification.file', ['verification' => $v->id, 'type' => 'cor']) }}"
                                       target="_blank" rel="noopener"
                                       class="text-blue-600 underline hover:text-blue-800">
                                        View COR
                                    </a>
                                @endif

                                @if ($hasCor && $hasId)
                                    <span class="text-gray-400 select-none">•</span>
                                @endif

                                @if ($hasId)
                                    <a href="{{ route('verification.file', ['verification' => $v->id, 'type' => 'id']) }}"
                                       target="_blank" rel="noopener"
                                       class="text-blue-600 underline hover:text-blue-800">
                                        View ID
                                    </a>
                                @endif
                            </div>

                            @if ($v->notes)
                                <div class="text-xs text-gray-500 mt-2">Notes: {{ $v->notes }}</div>
                            @endif

                            <div class="text-xs text-gray-400 mt-2">
                                Submitted: {{ optional($v->created_at)->format('Y-m-d H:i') }}
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button wire:click="approve({{ $v->id }})"
                                    class="px-3 py-1.5 rounded bg-blue-500 text-white text-sm hover:bg-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100">
                                Approve
                            </button>
                            <button wire:click="reject({{ $v->id }})"
                                    class="px-3 py-1.5 rounded bg-red-500 text-white text-sm hover:bg-red-600 focus:outline-none focus:ring-4 focus:ring-red-100">
                                Reject
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Approved --}}
    <section class="bg-white border rounded">
        <div class="px-4 py-3 font-semibold text-lg flex items-center justify-between">
            <span>Approved</span>
            <span class="text-xs text-gray-500">{{ $approved->count() }} total</span>
        </div>

        @if ($approved->isEmpty())
            <div class="px-4 py-3 text-sm text-gray-500">No approved students yet.</div>
        @else
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($approved as $v)
                    @php
                        $hasCor = (bool) $v->cor_file;
                        $hasId  = (bool) $v->id_file;

                        $sectionName = $v->section->name
                            ?? $v->section->section_name
                            ?? null;
                        $coordName   = $v->section->coordinator->name
                            ?? null;
                    @endphp

                    <article class="h-full rounded-lg border bg-white p-4 flex flex-col justify-between">
                        <div>
                            <div class="font-medium">
                                {{ $v->user?->name ?? 'Unknown User' }}
                                <span class="text-xs text-gray-500">({{ $v->user?->email ?? '—' }})</span>
                            </div>

                            {{-- NEW: Section & Coordinator --}}
                            @if ($sectionName || $coordName)
                                <div class="mt-1 text-sm text-gray-700 leading-5">
                                    @if ($sectionName)
                                        <div><span class="font-medium">Section:</span> {{ $sectionName }}</div>
                                    @endif
                                    @if ($coordName)
                                        <div><span class="font-medium">Coordinator:</span> {{ $coordName }}</div>
                                    @endif
                                </div>
                            @endif

                            {{-- FILE LINKS --}}
                            <div class="mt-2 text-sm inline-flex flex-wrap items-center gap-4">
                                @if ($hasCor)
                                    <a href="{{ route('verification.file', ['verification' => $v->id, 'type' => 'cor']) }}"
                                       target="_blank" rel="noopener"
                                       class="text-blue-600 underline hover:text-blue-800">
                                        View COR
                                    </a>
                                @endif
                                @if ($hasCor && $hasId)
                                    <span class="text-gray-400 select-none">•</span>
                                @endif
                                @if ($hasId)
                                    <a href="{{ route('verification.file', ['verification' => $v->id, 'type' => 'id']) }}"
                                       target="_blank" rel="noopener"
                                       class="text-blue-600 underline hover:text-blue-800">
                                        View ID
                                    </a>
                                @endif
                            </div>

                            <div class="text-xs text-gray-400 mt-2">
                                Reviewed by: {{ $v->reviewer?->name ?? '—' }}
                                • {{ optional($v->reviewed_at)->format('Y-m-d H:i') }}
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Rejected --}}
    <section class="bg-white border rounded">
        <div class="px-4 py-3 font-semibold text-lg flex items-center justify-between">
            <span>Rejected</span>
            <span class="text-xs text-gray-500">{{ $rejected->count() }} total</span>
        </div>

        @if ($rejected->isEmpty())
            <div class="px-4 py-3 text-sm text-gray-500">No rejected students.</div>
        @else
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($rejected as $v)
                    @php
                        $hasCor = (bool) $v->cor_file;
                        $hasId  = (bool) $v->id_file;

                        $sectionName = $v->section->name
                            ?? $v->section->section_name
                            ?? null;
                        $coordName   = $v->section->coordinator->name
                            ?? null;
                    @endphp

                    <article class="h-full rounded-lg border bg-white p-4 flex flex-col justify-between">
                        <div>
                            <div class="font-medium">
                                {{ $v->user?->name ?? 'Unknown User' }}
                                <span class="text-xs text-gray-500">({{ $v->user?->email ?? '—' }})</span>
                            </div>

                            {{-- NEW: Section & Coordinator --}}
                            @if ($sectionName || $coordName)
                                <div class="mt-1 text-sm text-gray-700 leading-5">
                                    @if ($sectionName)
                                        <div><span class="font-medium">Section:</span> {{ $sectionName }}</div>
                                    @endif
                                    @if ($coordName)
                                        <div><span class="font-medium">Coordinator:</span> {{ $coordName }}</div>
                                    @endif
                                </div>
                            @endif

                            {{-- FILE LINKS --}}
                            <div class="mt-2 text-sm inline-flex flex-wrap items-center gap-4">
                                @if ($hasCor)
                                    <a href="{{ route('verification.file', ['verification' => $v->id, 'type' => 'cor']) }}"
                                       target="_blank" rel="noopener"
                                       class="text-blue-600 underline hover:text-blue-800">
                                        View COR
                                    </a>
                                @endif
                                @if ($hasCor && $hasId)
                                    <span class="text-gray-400 select-none">•</span>
                                @endif
                                @if ($hasId)
                                    <a href="{{ route('verification.file', ['verification' => $v->id, 'type' => 'id']) }}"
                                       target="_blank" rel="noopener"
                                       class="text-blue-600 underline hover:text-blue-800">
                                        View ID
                                    </a>
                                @endif
                            </div>

                            <div class="text-xs text-gray-400 mt-2">
                                Reviewed by: {{ $v->reviewer?->name ?? '—' }}
                                • {{ optional($v->reviewed_at)->format('Y-m-d H:i') }}
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
