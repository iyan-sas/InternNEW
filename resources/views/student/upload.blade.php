{{-- resources/views/student/upload.blade.php --}}
@php
    use App\Models\AdminDocument;
    use App\Models\Section as SectionModel;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\Schema;

    // ========= Safe role check =========
    $roleRaw  = auth()->user()?->role ?? null;
    $roleStr  = is_object($roleRaw) && property_exists($roleRaw, 'value')
        ? strtolower($roleRaw->value)
        : strtolower((string) $roleRaw);
    $isStudent = auth()->check() && $roleStr === 'student';

    // ========= Stream guard =========
    /** @var \App\Models\Stream|null $stream */
    $hasStream = isset($stream) && $stream;

    // ========= Resolve section MODEL & section NAME & section ID (robust) =========
    $sectionModel = null;
    $resolvedSectionName = null;
    $resolvedSectionId   = null;

    if (isset($section) && $section) {
        if ($section instanceof SectionModel) {
            $sectionModel = $section;
        } elseif ($section instanceof Collection) {
            $first = $section->first();
            if ($first instanceof SectionModel) $sectionModel = $first;
        } elseif (is_array($section)) {
            $first = $section[0] ?? $section;
            $sid   = $first['id'] ?? null;
            if ($sid) $sectionModel = SectionModel::with('coordinator')->find($sid);
        }
    }

    // When only a name was passed, try to find the model
    if (!$sectionModel && isset($sectionName) && is_string($sectionName) && $hasStream) {
        $sectionModel = SectionModel::with('coordinator')
            ->where('stream_id', $stream->id)
            ->where(function ($q) use ($sectionName) {
                $q->where('section_name', $sectionName);
                if (Schema::hasColumn('sections', 'code')) $q->orWhere('code', $sectionName);
                if (Schema::hasColumn('sections', 'name')) $q->orWhere('name', $sectionName);
            })
            ->first();
    }

    // From stream->section relationship if available
    if (!$sectionModel && $hasStream && method_exists($stream, 'section') && $stream->relationLoaded('section')) {
        $sectionModel = $stream->section;
    }

    // Fallback: use session section_id
    if (!$sectionModel && session()->has('section_id')) {
        $sectionModel = SectionModel::with('coordinator')->find(session('section_id'));
    }

    // Name + ID finally
    if ($sectionModel instanceof SectionModel) {
        $resolvedSectionName = $sectionModel->section_name ?? ($sectionModel->name ?? ($sectionModel->code ?? null));
        $resolvedSectionId   = $sectionModel->id;
    }

    if (!$resolvedSectionName) {
        $resolvedSectionName = $hasStream
            ? ($stream->display_section ?? $stream->section ?? 'N/A')
            : 'N/A';
    }

    // ========= Coordinator name (section > stream) =========
    if ($sectionModel && method_exists($sectionModel, 'loadMissing')) $sectionModel->loadMissing('coordinator');
    if ($hasStream  && method_exists($stream, 'loadMissing'))        $stream->loadMissing('coordinator');

    $sectionCoordinatorName = $sectionModel?->coordinator?->name
        ?? $sectionModel?->coordinator_name
        ?? null;

    if (!$sectionCoordinatorName && $sectionModel?->coordinator_id) {
        $sectionCoordinatorName = \App\Models\User::where('id', $sectionModel->coordinator_id)->value('name');
    }

    $streamCoordinatorName = $stream->coordinator?->name
        ?? ($stream->coordinator_name ?? null);

    $resolvedCoordinatorName = $sectionCoordinatorName
        ?? $streamCoordinatorName
        ?? '—';

    // ========= Tabs & preload =========
    $initialTab = request('tab', 'home');

    $studentDocs = ($isStudent && $hasStream)
        ? AdminDocument::where('stream_id', $stream->id)->where('type', 'document')->latest()->get()
        : collect();

    $studentMoas = ($isStudent && $hasStream)
        ? AdminDocument::where('stream_id', $stream->id)->where('type', 'agreement')->latest()->get()
        : collect();

    // For components that need IDs
    $streamIdForComponents  = $hasStream ? (int) $stream->id : null;
    $sectionIdForComponents = $resolvedSectionId ?? (session('section_id') ? (int) session('section_id') : null);
@endphp

<x-layouts.app title="Student Dashboard">
    <style>[x-cloak]{display:none!important}</style>

    {{-- 🟢 Hidden context available to any JS/AJAX/Livewire submitters --}}
    <div id="verification-context"
         data-stream-id="{{ $streamIdForComponents }}"
         data-section-id="{{ $sectionIdForComponents }}"></div>

    {{-- If you need actual hidden inputs for a classic form submit, keep this tiny form. --}}
    <form id="verification-hidden-inputs" class="hidden" aria-hidden="true">
        @csrf
        @if($streamIdForComponents)
            <input type="hidden" name="stream_id" value="{{ $streamIdForComponents }}">
        @endif
        @if($sectionIdForComponents)
            <input type="hidden" name="section_id" value="{{ $sectionIdForComponents }}">
        @endif
    </form>

    {{-- prevent tiny horizontal overflow on phones --}}
    <div class="w-full max-w-7xl mx-auto p-3 sm:p-4 md:p-6 space-y-4 sm:space-y-6 overflow-x-hidden">

        {{-- ========== HERO HEADER ========== --}}
        <div class="relative w-full bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute inset-0 opacity-10 sm:opacity-10 lg:opacity-[0.06]"
                     style="background-image: radial-gradient(circle at 2px 2px, rgb(37, 99, 235) 1px, transparent 0); background-size: 24px 24px;">
                </div>
            </div>

            <div class="relative z-10 p-4 sm:p-5 lg:p-6">
                <div class="mx-auto max-w-5xl">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
                        <div class="space-y-2 sm:space-y-3">
                            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-blue-900">
                                {{ __('Student Dashboard') }}
                            </h1>

                            <p class="text-sm sm:text-base text-blue-700/80 leading-relaxed">
                                {{ __('Access your class updates, tasks, and uploads from here') }}
                                <span class="block sm:inline font-semibold">
                                    {{ $hasStream ? ($stream->campus ?? 'Campus') : 'Campus' }} — {{ $hasStream ? ($stream->college ?? 'College') : 'College' }}
                                </span>
                                <span class="block sm:inline">
                                    {{ __('Section:') }} <span class="font-semibold">{{ $resolvedSectionName }}</span>
                                </span>
                                <span class="block sm:inline">
                                    {{ __('Coordinator:') }} <span class="font-semibold">{{ $resolvedCoordinatorName }}</span>
                                </span>
                            </p>

                            {{-- Email + Active chips (truncate on mobile) --}}
                            <div class="flex flex-wrap gap-2 pt-1">
                                <span class="inline-flex items-center max-w-[260px] sm:max-w-none truncate px-3 py-1 rounded-full text-xs sm:text-sm lg:text-[13.5px] font-medium bg-blue-200 text-blue-900">
                                    <svg class="w-3 h-3 mr-1 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                                    </svg>
                                    <span class="truncate">{{ auth()->user()->email }}</span>
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs sm:text-sm lg:text-[13.5px] font-medium bg-emerald-200 text-emerald-800">
                                    <span class="w-2 h-2 bg-emerald-500 rounded-full mr-1 animate-pulse"></span>
                                    {{ __('Active') }}
                                </span>
                            </div>
                        </div>
                        {{-- (optional) right column space --}}
                    </div>
                </div>
            </div>
        </div>
        {{-- ========== /HERO HEADER ========== --}}

        {{-- ========== MAIN CARD (Tabs + Content) ========== --}}
        <div
            class="bg-white border border-zinc-200 rounded-2xl shadow-sm"
            x-data="{
                tab: '{{ $initialTab }}',
                setTab(t){
                    this.tab = t;
                    const u = new URL(window.location);
                    u.searchParams.set('tab', t);
                    history.replaceState({}, '', u);
                }
            }"
            x-init="$watch('tab', v => { if (v === 'home') { Livewire.dispatch('refresh') } })"
        >
            {{-- Tabs header --}}
            <div class="px-2 sm:px-4 py-2 border-b">
                <div class="mx-auto max-w-5xl">
                    <div class="-mx-2 overflow-x-auto">
                        <nav class="flex min-w-max gap-2 sm:gap-3 px-2 py-1">
                            <button
                                type="button"
                                @click="setTab('home')"
                                class="whitespace-nowrap inline-flex items-center justify-center px-4 py-2 rounded-full border text-sm sm:text-base transition"
                                :class="tab==='home'
                                    ? 'bg-blue-600 text-white border-blue-600'
                                    : 'bg-white text-zinc-700 hover:bg-blue-50 hover:text-blue-700 border-zinc-200'">
                                {{ __('Home') }}
                            </button>

                            <button
                                type="button"
                                @click="setTab('documents')"
                                class="whitespace-nowrap inline-flex items-center justify-center px-4 py-2 rounded-full border text-sm sm:text-base transition"
                                :class="tab==='documents'
                                    ? 'bg-blue-600 text-white border-blue-600'
                                    : 'bg-white text-zinc-700 hover:bg-blue-50 hover:text-blue-700 border-zinc-200'">
                                {{ __('Copy of Documents') }}
                            </button>

                            <button
                                type="button"
                                @click="setTab('moa')"
                                class="whitespace-nowrap inline-flex items-center justify-center px-4 py-2 rounded-full border text-sm sm:text-base transition"
                                :class="tab==='moa'
                                    ? 'bg-blue-600 text-white border-blue-600'
                                    : 'bg-white text-zinc-700 hover:bg-blue-50 hover:text-blue-700 border-zinc-200'">
                                {{ __('Copy of Memorandum Agreement') }}
                            </button>
                        </nav>
                    </div>
                </div>
            </div>

            {{-- Panels --}}
            <div class="p-3 sm:p-4 lg:p-6">
                <div class="mx-auto max-w-5xl space-y-6">

                    {{-- HOME --}}
                    <section x-show="tab==='home'" x-cloak class="space-y-6">
                        {{-- Announcements --}}
                        <div class="bg-white border rounded-2xl shadow-sm">
                            <div class="px-4 py-3 border-b">
                                <h2 class="text-lg sm:text-xl font-semibold text-zinc-900">{{ __('Announcements') }}</h2>
                            </div>
                            <div class="p-4">
                                @if($hasStream)
                                    <livewire:announcements-list
                                        :stream-id="$streamIdForComponents"
                                        :section-id="$sectionIdForComponents"
                                        :read-only="true"
                                    />
                                @else
                                    <div class="text-sm text-zinc-600">{{ __('Stream not found.') }}</div>
                                @endif
                            </div>
                        </div>

                        {{-- OJT Information Form --}}
                        @auth
                            @if($isStudent && $hasStream)
                                <div class="bg-white border rounded-2xl shadow-sm p-4 sm:p-5">
                                    <h3 class="text-base sm:text-lg font-semibold text-zinc-900 mb-3">{{ __('OJT Information') }}</h3>
                                    @livewire('student.ojt-information-form', [
                                        'streamId'  => $streamIdForComponents,
                                        'sectionId' => $sectionIdForComponents,
                                    ], key('ojt-form-'.$streamIdForComponents.'-'.($sectionIdForComponents ?? '0')))
                                </div>
                            @endif
                        @endauth

                        {{-- Tasks --}}
                        <div class="bg-white border rounded-2xl shadow-sm p-4 sm:p-5">
                            <h3 class="text-base sm:text-lg font-semibold text-zinc-900 mb-3">{{ __('Tasks') }}</h3>
                            @if(!$hasStream || !isset($tasks) || (method_exists($tasks ?? null, 'isEmpty') && $tasks->isEmpty()))
                                <p class="text-sm text-zinc-600">{{ __('No tasks assigned yet.') }}</p>
                            @else
                                @livewire('task-list', [
                                    'tasks'       => $tasks,
                                    'stream_id'   => $streamIdForComponents,
                                    'section_id'  => $sectionIdForComponents,
                                ], key('tasklist-'.$streamIdForComponents.'-'.($sectionIdForComponents ?? '0')))
                            @endif
                        </div>
                    </section>

                    {{-- DOCUMENTS --}}
                    <section x-show="tab==='documents'" x-cloak class="space-y-4">
                        <div class="bg-white border rounded-2xl shadow-sm">
                            <div class="px-4 py-3 border-b">
                                <h2 class="text-lg sm:text-xl font-semibold text-zinc-900">{{ __('Copy of Documents') }}</h2>
                            </div>
                            <div class="p-4">
                                @if ($isStudent)
                                    @forelse ($studentDocs as $doc)
                                        <div class="border rounded-xl p-3 sm:p-4 mb-3">
                                            <div class="font-semibold text-zinc-900">
                                                {{ $doc->title }}
                                            </div>

                                            @if(filled($doc->description))
                                                <div class="text-sm text-zinc-700 mt-1 break-words">
                                                    {{ $doc->description }}
                                                </div>
                                            @endif

                                            <div class="text-xs text-zinc-500 mt-1 break-all">
                                                ID: {{ $doc->id }} • {{ ucfirst($doc->type) }} • {{ $doc->filename }}
                                            </div>

                                            <div class="mt-2">
                                                <a href="{{ route('files.show', $doc->id) }}" class="text-blue-600 underline" target="_blank" rel="noopener">
                                                    {{ __('View File') }}
                                                </a>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-sm text-zinc-600">{{ __('No files uploaded yet.') }}</p>
                                    @endforelse
                                @else
                                    @if($hasStream)
                                        @livewire('admin-upload', [
                                            'streamId' => $streamIdForComponents,
                                            'type'     => 'document',
                                        ])
                                    @else
                                        <p class="text-sm text-zinc-600">{{ __('Stream not found.') }}</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </section>

                    {{-- MOA --}}
                    <section x-show="tab==='moa'" x-cloak class="space-y-4">
                        <div class="bg-white border rounded-2xl shadow-sm">
                            <div class="px-4 py-3 border-b">
                                <h2 class="text-lg sm:text-xl font-semibold text-zinc-900">{{ __('Copy of Memorandum Agreement') }}</h2>
                            </div>
                            <div class="p-4">
                                @if ($isStudent)
                                    @forelse ($studentMoas as $doc)
                                        <div class="border rounded-xl p-3 sm:p-4 mb-3">
                                            <div class="font-semibold text-zinc-900">
                                                {{ $doc->title }}
                                            </div>

                                            @if(filled($doc->description))
                                                <div class="text-sm text-zinc-700 mt-1 break-words">
                                                    {{ $doc->description }}
                                                </div>
                                            @endif

                                            <div class="text-xs text-zinc-500 mt-1 break-all">
                                                ID: {{ $doc->id }} • {{ ucfirst($doc->type) }} • {{ $doc->filename }}
                                            </div>

                                            <div class="mt-2">
                                                <a href="{{ route('files.show', $doc->id) }}" class="text-blue-600 underline" target="_blank" rel="noopener">
                                                    {{ __('View File') }}
                                                </a>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-sm text-zinc-600">{{ __('No agreement files uploaded yet.') }}</p>
                                    @endforelse
                                @else
                                    @if($hasStream)
                                        @livewire('admin-upload', [
                                            'streamId' => $streamIdForComponents,
                                            'type'     => 'agreement',
                                        ])
                                    @else
                                        <p class="text-sm text-zinc-600">{{ __('Stream not found.') }}</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </section>

                </div>
            </div>
        </div>
        {{-- ========== /MAIN CARD ========== --}}
    </div>
</x-layouts.app>
