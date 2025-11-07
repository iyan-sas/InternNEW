{{-- resources/views/sections/show.blade.php --}}

@php
    use App\Models\AdminDocument;
    use App\Models\OjtProfile;
    use Illuminate\Support\Facades\Schema;

    // Ensure needed relations are loaded
    $section->loadMissing('stream', 'students');

    // Campus & College (columns exist on streams)
    $campus  = $stream->campus  ?? $stream->campus_name  ?? 'Campus';
    $college = $stream->college ?? $stream->college_name ?? 'College';

    // Archived flag (controls view-only state)
    $viewOnly = (bool) ($section->archived ?? false);

    // Prefer section token; fallback to stream token
    $studentToken = $section->student_upload_token ?? $stream->student_token;
    $studentUrl   = $studentToken ? route('section.student.join', ['token' => $studentToken]) : null;

    // Initial tab (from ?tab=)
    $initialTab   = request('tab', 'home');

    // Normalize user role
    $roleRaw = auth()->user()?->role;
    $roleStr = is_object($roleRaw) && property_exists($roleRaw, 'value')
        ? strtolower($roleRaw->value)
        : strtolower((string) $roleRaw);

    // Helper: fetch admin uploads scoped to this stream + section
    $fetchDocs = function (string $type) use ($stream, $section) {
        return AdminDocument::where('stream_id', $stream->id)
            ->where('section_id', $section->id)
            ->where('type', $type)
            ->orderByDesc('id')
            ->get();
    };

    // Do we have users.student_no column?
    $hasStudentNo = Schema::hasColumn('users', 'student_no');
@endphp

<x-layouts.app :title="$campus . ' — ' . $college">
    <style>
        [x-cloak]{display:none!important}
        @media (max-width: 767.98px){
            .mobile-x::-webkit-scrollbar{display:none}
            .mobile-x{scrollbar-width:none}
        }
    </style>

    <div class="space-y-4"
         x-data="{
            tab: '{{ $initialTab }}',
            copiedStud:false,
            setTab(t){
                this.tab = t;
                const u = new URL(window.location);
                u.searchParams.set('tab', t);
                window.history.replaceState({}, '', u);
            }
         }">

        {{-- Header --}}
        <div class="bg-white border rounded-xl p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold">{{ $campus }} — {{ $college }}</h1>
                    <p class="text-sm text-gray-500">
                        Section: <span class="font-medium">{{ $section->section_name }}</span>
                        • Coordinator: {{ $section->coordinator?->name ?? $section->coordinator_name ?? '—' }}
                    </p>

                    {{-- Quick Info Pills --}}
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-200 text-blue-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            {{ auth()->user()->email }}
                        </span>

                        @if(!$viewOnly)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-200 text-green-800">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-1 animate-pulse"></span>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 border border-amber-200">
                                Archived • View-only
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Student Upload Link (hidden if archived) --}}
            <div class="mt-3 space-y-2 text-sm">
                @if ($studentUrl && !$viewOnly)
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-semibold">Student Upload Link:</span>
                        <a href="{{ $studentUrl }}" target="_blank" rel="noopener"
                           class="text-blue-600 hover:text-blue-800 underline break-all">
                            {{ $studentUrl }}
                        </a>
                        <button type="button"
                                class="ml-2 px-2 py-0.5 text-xs rounded bg-blue-600 text-white hover:bg-blue-700"
                                x-on:click="navigator.clipboard.writeText('{{ $studentUrl }}'); copiedStud=true; setTimeout(()=>copiedStud=false,1500)">
                            Copy
                        </button>
                        <span x-show="copiedStud" x-transition class="ml-1 text-xs text-green-600">Copied!</span>
                    </div>
                @elseif($viewOnly)
                    <div class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                        This section is archived. New student joins/uploads are disabled.
                    </div>
                @else
                    <div class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                        No student upload token found for this section. Please generate one first.
                    </div>
                @endif
            </div>
        </div>

        {{-- Tabs --}}
        <div class="mt-2 bg-white border border-gray-300 rounded-md px-6 py-3 shadow-sm w-full">
            <div class="p-2">
                <nav
                    class="mobile-x flex gap-2 overflow-x-auto
                           md:overflow-visible md:flex md:justify-center md:flex-wrap md:gap-3
                           rounded-md bg-blue-50/60 px-2 py-2"
                    aria-label="Section tabs"
                >
                    @php
                        $base = 'shrink-0 flex-none text-center
                                 px-5 py-2 rounded-full text-sm font-medium transition-colors
                                 focus:outline-none ring-0';
                        $active = 'bg-blue-600 text-white shadow-sm';
                        $idle   = 'text-slate-700 hover:bg-white/60';
                    @endphp

                    <button x-on:click="setTab('home')" class="{{ $base }}" :class="tab==='home' ? '{{ $active }}' : '{{ $idle }}'">Home</button>
                    <button x-on:click="setTab('docs')" class="{{ $base }}" :class="tab==='docs' ? '{{ $active }}' : '{{ $idle }}'">Copy of Documents</button>
                    <button x-on:click="setTab('moa')" class="{{ $base }}" :class="tab==='moa' ? '{{ $active }}' : '{{ $idle }}'">Copy of Memorandum Agreement</button>
                    <button x-on:click="setTab('students')" class="{{ $base }}" :class="tab==='students' ? '{{ $active }}' : '{{ $idle }}'">Students</button>
                    <button x-on:click="setTab('ojt')" class="{{ $base }}" :class="tab==='ojt' ? '{{ $active }}' : '{{ $idle }}'">OJT Forms</button>
                </nav>
            </div>
        </div>

        {{-- Content --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6" x-cloak>
            {{-- Home --}}
            <section x-show="tab === 'home'" x-transition.opacity.duration.200ms>
                {{-- Announcements: hide form when archived; list stays --}}
                @if (!$viewOnly && in_array($roleStr, ['admin','coordinator']))
                    <livewire:announcement-form
                      :stream-id="$stream->id"
                      :section-id="$section->id ?? null"
                      :can-manage="true"
                    />
                @endif

                <livewire:announcements-list
                  :stream-id="$stream->id"
                  :section-id="$section->id"
                />

                {{-- Student OJT self-form: only for students, always allowed to VIEW; editing is handled in that component/policy --}}
                @if ($roleStr === 'student')
                    <div class="mt-6">
                        @livewire('student.ojt-information-form', [
                            'streamId'  => $stream->id,
                            'sectionId' => $section->id
                        ], key('ojt-info-'.$stream->id.'-'.$section->id))
                    </div>
                @endif

                <div class="mt-6 space-y-4">
                    {{-- Tasks: hide Task Form when archived; keep Task List visible --}}
                    @if (!$viewOnly && in_array($roleStr, ['admin','coordinator']))
                        @livewire('task-form', ['stream_id' => $stream->id, 'section_id' => $section->id], key('task-form-'.$stream->id.'-'.$section->id))
                    @endif

                    @livewire('task-list', ['stream_id' => $stream->id, 'section_id' => $section->id], key('task-list-'.$stream->id.'-'.$section->id))
                </div>
            </section>

            {{-- Copy of Documents --}}
            <section x-show="tab === 'docs'" x-transition.opacity.duration.200ms wire:ignore.self>
                @if (!$viewOnly && in_array($roleStr, ['admin','coordinator']))
                    {{-- Editable upload only when NOT archived --}}
                    @livewire('admin-upload', [
                        'streamId'  => $stream->id,
                        'sectionId' => $section->id,
                        'type'      => 'document'
                    ], key('admin-upload-document-'.$stream->id.'-'.$section->id))
                @else
                    @php $docs = $fetchDocs('document'); @endphp
                    <div class="space-y-3">
                        @if ($docs->count())
                            @foreach ($docs as $doc)
                                <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="font-semibold text-gray-900">{{ $doc->title }}</div>
                                    @if (filled($doc->description))
                                        <div class="text-sm text-gray-700 mt-0.5">{{ $doc->description }}</div>
                                    @endif
                                    <div class="text-xs text-gray-500 mt-1">ID: {{ $doc->id }} | File: {{ $doc->filename }}</div>
                                    <div class="mt-2">
                                        <a href="{{ route('files.show', $doc->id) }}" class="text-blue-600 hover:text-blue-800 underline font-medium" target="_blank" rel="noopener">View File</a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-600">No files uploaded yet.</p>
                        @endif
                    </div>
                @endif
            </section>

            {{-- MOA --}}
            <section x-show="tab === 'moa'" x-transition.opacity.duration.200ms wire:ignore.self>
                @if (!$viewOnly && in_array($roleStr, ['admin','coordinator']))
                    {{-- Editable upload only when NOT archived --}}
                    @livewire('admin-upload', [
                        'streamId'  => $stream->id,
                        'sectionId' => $section->id,
                        'type'      => 'agreement'
                    ], key('admin-upload-agreement-'.$stream->id.'-'.$section->id))
                @else
                    @php $agreements = $fetchDocs('agreement'); @endphp
                    <div class="space-y-3">
                        @if ($agreements->count())
                            @foreach ($agreements as $doc)
                                <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="font-semibold text-gray-900">{{ $doc->title }}</div>
                                    @if (filled($doc->description))
                                        <div class="text-sm text-gray-700 mt-0.5">{{ $doc->description }}</div>
                                    @endif
                                    <div class="text-xs text-gray-500 mt-1">ID: {{ $doc->id }} | File: {{ $doc->filename }}</div>
                                    <div class="mt-2">
                                        <a href="{{ route('files.show', $doc->id) }}" class="text-blue-600 hover:text-blue-800 underline font-medium" target="_blank" rel="noopener">View File</a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-600">No agreement files uploaded yet.</p>
                        @endif
                    </div>
                @endif
            </section>

            {{-- Students (read-only; not clickable) --}}
            <section x-show="tab === 'students'" x-transition.opacity.duration.200ms>
                <h2 class="text-lg font-semibold mb-4 text-gray-900">Students</h2>

                {{-- Hide the import/add UI when archived --}}
                @if (!$viewOnly && in_array($roleStr, ['admin','coordinator']))
                    @livewire(\App\Livewire\Coordinator\StudentsImport::class, ['sectionId' => $section->id], key('import-'.$section->id))
                @endif

                @php
                    $students = collect();

                    if (Schema::hasTable('section_user') && method_exists($section, 'students')) {
                        $q = $section->students()->select('users.id','users.name','users.email');
                        if ($hasStudentNo) $q->addSelect('users.student_no');
                        $students = $q->orderBy('users.name')->get();
                    } elseif (Schema::hasTable('stream_user')) {
                        $q = \App\Models\User::query()
                            ->whereHas('streams', function ($q2) use ($stream, $section) {
                                $q2->where('stream_user.stream_id', $stream->id)
                                   ->where('stream_user.section_id', $section->id);
                            })
                            ->select('users.id','users.name','users.email');
                        if ($hasStudentNo) $q->addSelect('users.student_no');

                        $students = $q->orderBy('users.name')->get()
                            ->map(function ($u) use ($stream, $section) {
                                $pivot = \DB::table('stream_user')
                                    ->where('stream_id', $stream->id)
                                    ->where('section_id', $section->id)
                                    ->where('user_id', $u->id)
                                    ->first();
                                $u->pivot = (object)[
                                    'status'    => $pivot->status ?? 'pending',
                                    'joined_at' => $pivot->joined_at ?? null,
                                ];
                                return $u;
                            });
                    }
                @endphp

                @if ($students->isNotEmpty())
                    <div class="space-y-2 mt-6" data-roster>
                        @foreach ($students as $stu)
                            @php
                                $status   = strtolower((string) ($stu->pivot->status ?? 'pending'));
                                $badgeCls = $status === 'approved'
                                    ? 'bg-green-100 text-green-700'
                                    : ($status === 'rejected'
                                        ? 'bg-red-100 text-red-700'
                                        : 'bg-yellow-100 text-yellow-700');
                            @endphp

                            <div
                                class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-200 select-text cursor-default"
                                x-data
                                x-on:click.stop
                                x-on:mousedown.stop
                                x-on:pointerdown.stop
                            >
                                <span class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900">{{ $stu->name }}</span>
                                    <span class="text-gray-500 text-sm">({{ $stu->email }})</span>
                                    <span class="ml-2 text-xs px-2 py-0.5 rounded-full {{ $badgeCls }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
                        <p class="text-sm text-blue-800">
                            <strong>No students found in this section.</strong>
                        </p>
                    </div>
                @endif
            </section>

            {{-- OJT Forms (submitted only; clickable) --}}
            <section x-show="tab === 'ojt'" x-transition.opacity.duration.200ms>
                <h2 class="text-lg font-semibold mb-4 text-gray-900">OJT Forms (Submitted)</h2>

                @php
                    $filled = OjtProfile::query()
                        ->join('users', 'users.id', '=', 'ojt_profiles.user_id')
                        ->where('ojt_profiles.stream_id', $stream->id)
                        ->where('ojt_profiles.section_id', $section->id)
                        ->orderBy('users.name')
                        ->get([
                            'ojt_profiles.user_id',
                            'ojt_profiles.stream_id',
                            'ojt_profiles.section_id',
                            'users.name',
                            'users.email',
                            $hasStudentNo ? 'users.student_no' : \DB::raw('NULL as student_no'),
                        ]);
                @endphp

                @if ($filled->isEmpty())
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <p class="text-amber-500 text-sm">No submitted OJT forms yet.</p>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach ($filled as $row)
                            @php
                                $uid = (int) $row->user_id;
                                $payload = [
                                    'userId' => $uid, 'user_id' => $uid,
                                    'streamId' => (int) $row->stream_id, 'stream_id' => (int) $row->stream_id,
                                    'sectionId' => (int) $row->section_id, 'section_id' => (int) $row->section_id,
                                    'fullName' => (string) $row->name,
                                    'studentNo' => $hasStudentNo ? (string) ($row->student_no ?? '') : '',
                                    'email' => (string) ($row->email ?? ''),
                                ];
                            @endphp
                            <div
                                class="flex justify-between items-center bg-white hover:bg-gray-50 p-3 rounded-lg border border-gray-200 transition-colors cursor-pointer"
                                data-payload='@json($payload)'
                                x-data
                                x-on:click.stop="
                                    let p = JSON.parse($el.getAttribute('data-payload'));
                                    $dispatch('ojt:view', p);
                                    if (window.Livewire && Livewire.dispatch) Livewire.dispatch('ojt:open', p);
                                "
                            >
                                <div class="flex flex-col">
                                    <span class="font-medium text-blue-700 underline">{{ $row->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $row->email }}</span>
                                </div>
                                @if($hasStudentNo)
                                    <span class="text-xs text-gray-600">Student No: {{ $row->student_no ?? '—' }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        {{-- Hard guard: block any modal open if click originated inside Students roster --}}
        <script>
            (function(){
                const roster = document.querySelector('[data-roster]');
                if (!roster) return;

                // mark when a click started inside roster (capture phase)
                window.addEventListener('click', function(e){
                    if (e.target.closest('[data-roster]')) {
                        window.__blockOJT = Date.now();
                    }
                }, true);

                // if an ojt:view event fires immediately after a roster click, cancel it
                window.addEventListener('ojt:view', function(e){
                    if (window.__blockOJT && (Date.now() - window.__blockOJT) < 400) {
                        e.stopImmediatePropagation && e.stopImmediatePropagation();
                    }
                }, true);
            })();
        </script>

        {{-- Modal component (listens to ojt:view / Livewire dispatch) --}}
        @livewire('coordinator.student-ojt-viewer', [
          'sectionId' => $section->id,
          'streamId'  => $stream->id,
        ], key('ojt-viewer-'.$stream->id.'-'.$section->id))
    </div>
</x-layouts.app>
