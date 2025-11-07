{{-- resources/views/components/layouts/app/sidebar.blade.php --}}
{{-- Classroom-style: full menu when open; skinny 72px icon rail when closed --}}
{{-- Parent provides x-data="{ open: ... }" (see app.blade.php) --}}
{{-- In <head>: <style>[x-cloak]{display:none!important}</style> --}}

{{-- 🖤 Scrim (mobile only) --}}
<div
    x-show="open"
    x-transition.opacity
    class="fixed inset-0 z-40 bg-black/40 lg:hidden"
    @click="open = false"
    x-cloak
></div>

@php
    use App\Models\User;
    use Illuminate\Support\Facades\Route as RouteFacade;
    use Illuminate\Support\Facades\Schema;

    $user     = auth()->user();
    $token    = session('stream_token');
    $streamId = session('stream_id'); // ✅ primary source for student dashboard

    // Normalize role -> string (also handle "Super Admin" / "super_admin")
    $roleStr = null;
    if ($user) {
        $roleRaw = $user->role ?? null;
        $roleStr = is_object($roleRaw) && property_exists($roleRaw, 'value')
            ? strtolower((string) $roleRaw->value)
            : strtolower((string) $roleRaw);
        $roleStr = preg_replace('/\s+/', '_', $roleStr ?? '');
    }

    // Super Admin flag (either role contains 'super' or legacy is_creator = true)
    $isSuper   = str_contains($roleStr ?? '', 'super') || (bool)($user->is_creator ?? false);
    $showAdmin = ($roleStr === 'admin') && !$isSuper;

    // Fallback: resolve stream id using token only if not set in session
    if (!$streamId && !empty($token)) {
        $streamId = \App\Models\Stream::where('student_token', $token)
            ->orWhere('invite_token', $token)
            ->value('id');
    }

    /* ===========================
       PENDING COUNTS (model scope)
       =========================== */

    // Admin: count pending coordinators
    $pendingAdmin = 0;
    if ($showAdmin) {
        $pendingAdmin = User::pendingCoordinators()->count();
    }

    // Coordinator: pending students in this stream
    $pendingCoord = 0;
    if (($roleStr === 'coordinator') && $streamId) {
        $pendingCoord = \App\Models\StudentVerification::query()
            ->where('stream_id', $streamId)
            ->where('status', 'pending')
            ->count();
    }

    // Super Admin: count pending admin sign-ups
    $pendingAdminsToApprove = 0;
    if ($isSuper) {
        $pendingAdminsToApprove = User::query()
            ->when(Schema::hasColumn('users','is_approved'), fn($q) => $q->where('is_approved', false))
            ->when(Schema::hasColumn('users','status'), fn($q) => $q->where('status','pending'))
            ->when(Schema::hasColumn('users','role'), fn($q) => $q->where('role','admin'))
            ->count();
    }

    // Home route
    $homeRoute = route('home');
    if ($showAdmin) {
        $homeRoute = route('dashboard');
    } elseif ($roleStr === 'coordinator') {
        $homeRoute = $token ? route('coordinator.dashboard', ['token' => $token]) : route('dashboard');
    } elseif ($roleStr === 'student') {
        // ✅ go straight to class dashboard if we know the stream id
        $homeRoute = $streamId
            ? route('student.class.show', ['streamId' => $streamId])
            : route('student.dashboard');
    } elseif ($isSuper) {
        $homeRoute = RouteFacade::has('manage.pending-admins')
            ? route('manage.pending-admins')
            : (RouteFacade::has('admin.users.pending') ? route('admin.users.pending') : route('home'));
    }

    // Active helpers
    $isAdminDash   = request()->routeIs('dashboard');
    $isAdminCal    = request()->routeIs('calendar');
    $isAdminCoord  = request()->routeIs('coordinator');
    $isAdminHub    = request()->routeIs('admin.coordinators.hub');

    $isCoordDash      = str_contains(request()->url(), '/coordinator/dashboard');
    $isCoordCal       = request()->routeIs('coordinator.calendar');
    $isCoordPend      = request()->routeIs('coordinator.verifications');
    $isCoordArchived  = request()->routeIs('coordinator.sections.archived');

    // ✅ treat both student.class.show and student.dashboard as "Dashboard" active
    $isStudDash = request()->routeIs('student.class.show') || request()->routeIs('student.dashboard');
    $isStudCal  = request()->routeIs('student.calendar');

    $isSuperApprovals = request()->routeIs('manage.pending-admins') || request()->routeIs('admin.users.pending');

    /* ===========================
       SHARED CLASSES
       =========================== */
    $iconCls   = 'h-6 w-6';
    $labelCls  = 'font-semibold';

    // OPEN state links
    $activeLink = 'bg-blue-600 text-white [&_svg]:text-white [&_span]:text-white';
    $idleLink   = 'hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-800 dark:text-zinc-100';

    // CLOSED rail pills
    $pill      = 'relative flex h-12 w-12 items-center justify-center rounded-2xl transition-colors duration-200';
    $activePil = 'bg-blue-600 text-white';
    $idlePil   = 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800';

    // Detect if current route is the Student Verification page
    $currentRoute = request()->route()->getName();
    $isVerificationPage = str_starts_with($currentRoute, 'student.verify');
@endphp

{{-- 🧊 Sidebar shell --}}
<flux:sidebar
    class="fixed left-0 top-0 z-50 h-screen bg-blue-200 dark:bg-zinc-900/90 backdrop-blur border-e border-zinc-500 dark:border-zinc-800
           flex flex-col transition-all duration-300 ease-in-out p-0"
    x-data="{
        screenIsMobile: window.innerWidth < 1024,
        init() {
            this.screenIsMobile = window.innerWidth < 1024;
            window.addEventListener('resize', () => {
                this.screenIsMobile = window.innerWidth < 1024;
            });
        }
    }"
    x-init="init()"
    x-bind:style="screenIsMobile
        ? (open ? 'width:100vw; max-width:100%;' : 'width:72px;')
        : (open ? 'width:256px;' : 'width:72px;')"
>
    {{-- 🔘 Toggle --}}
    <button
        type="button"
        @click="open = !open"
        class="absolute right-3 top-3 inline-flex h-9 w-9 items-center justify-center rounded-md bg-blue-600 text-white shadow focus:outline-none"
        aria-label="Toggle menu"
        title="Toggle menu"
    >
        <svg x-show="!open" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/>
        </svg>
        <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <div class="h-4"></div>

    {{-- Logo --}}
    <a href="{{ $homeRoute }}" class="flex items-center px-4 py-3 mt-10 space-x-3">
        <x-app-logo />
        <span x-show="open" x-cloak class="text-sm {{ $labelCls }}">Platform</span>
    </a>

    {{-- =========================
         A) FULL MENU (OPEN)
       ========================= --}}
    <div x-show="open" x-cloak class="flex-1 overflow-y-auto">
        @auth
            <nav class="px-2 py-2 space-y-1">

                {{-- SUPER ADMIN --}}
                @if ($isSuper)
                    <a href="{{ RouteFacade::has('manage.pending-admins') ? route('manage.pending-admins') : (RouteFacade::has('admin.users.pending') ? route('admin.users.pending') : '#') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $isSuperApprovals ? $activeLink : $idleLink }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-1a4 4 0 0 0-4-4h0a4 4 0 0 0-4 4v1"/>
                            <circle cx="12" cy="10" r="3.5"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 8v6m3-3h-6"/>
                        </svg>
                        <span class="{{ $labelCls }}">Pending Admin Approvals</span>
                        @if ($pendingAdminsToApprove > 0)
                            <span class="ml-auto min-w-[22px] h-[20px] px-1.5 inline-flex items-center justify-center text-[11px] leading-[20px] font-semibold rounded-full bg-red-600 text-white shadow">
                                {{ $pendingAdminsToApprove }}
                            </span>
                        @endif
                    </a>
                @endif

                {{-- ADMIN (hidden for super admin) --}}
                @if ($showAdmin)
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $isAdminDash ? $activeLink : $idleLink }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 10.5V21h14v-10.5"/>
                        </svg>
                        <span class="{{ $labelCls }}">Dashboard</span>
                    </a>

                    <a href="{{ route('calendar') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $isAdminCal ? $activeLink : $idleLink }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v3M8 2v3M3 8h18"/>
                            <rect x="3" y="5" width="18" height="16" rx="2" ry="2" />
                        </svg>
                        <span class="{{ $labelCls }}">Calendar</span>
                    </a>

                    <a href="{{ route('coordinator') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $isAdminCoord ? $activeLink : $idleLink }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-1a4 4 0 0 0-4-4h0a4 4 0 0 0-4 4v1"/>
                            <circle cx="12" cy="10" r="3.5"/>
                        </svg>
                        <span class="{{ $labelCls }}">Coordinator</span>
                    </a>

                    <a href="{{ route('admin.coordinators.hub') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $isAdminHub ? $activeLink : $idleLink }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="8" height="8" rx="2"/>
                            <rect x="13" y="3" width="8" height="8" rx="2"/>
                            <rect x="3" y="13" width="8" height="8" rx="2"/>
                            <rect x="13" y="13" width="8" height="8" rx="2"/>
                        </svg>
                        <span class="{{ $labelCls }}">Coordinator Hub</span>
                        @if ($pendingAdmin > 0)
                            <span class="ml-auto min-w-[22px] h-[20px] px-1.5 inline-flex items-center justify-center text-[11px] leading-[20px] font-semibold rounded-full bg-red-500 text-white shadow">
                                {{ $pendingAdmin }}
                            </span>
                        @endif
                    </a>
                @endif

                {{-- COORDINATOR --}}
                @if ($roleStr === 'coordinator')
                    <a href="{{ route('coordinator.dashboard', ['token' => $token]) }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $isCoordDash ? $activeLink : $idleLink }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 10.5V21h14v-10.5"/>
                        </svg>
                        <span class="{{ $labelCls }}">Dashboard</span>
                    </a>

                    <a href="{{ route('coordinator.calendar') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $isCoordCal ? $activeLink : $idleLink }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v3M8 2v3M3 8h18"/>
                            <rect x="3" y="5" width="18" height="16" rx="2" ry="2" />
                        </svg>
                        <span class="{{ $labelCls }}">Calendar</span>
                    </a>

                    @if ($streamId)
                        <a href="{{ route('coordinator.verifications', ['streamId' => $streamId]) }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $isCoordPend ? $activeLink : $idleLink }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                             <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-1a4 4 0 0 0-4-4h0a4 4 0 0 0-4 4v1"/>
                             <circle cx="12" cy="10" r="3.5"/>
                             <path stroke-linecap="round" stroke-linejoin="round" d="M19 8v6m3-3h-6"/>
                           </svg>
                         <span class="{{ $labelCls }}">Pending Students</span>

                         {{-- badge sa kanan gaya ng Admin --}}
                            @if ($pendingCoord > 0)
                             <span class="ml-auto min-w-[22px] h-[20px] px-1.5 inline-flex items-center justify-center text-[11px] leading-[20px] font-semibold rounded-full bg-red-500 text-white shadow">
                             {{ $pendingCoord }}
                             </span>
                            @endif
                       </a>



                        {{-- ⭐ NEW: Archived Sections --}}
                        <a href="{{ route('coordinator.sections.archived', ['stream' => $streamId]) }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $isCoordArchived ? $activeLink : $idleLink }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18l-2 12H5L3 7z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2"/>
                            </svg>
                            <span class="{{ $labelCls }}">Archived Sections</span>
                        </a>
                    @endif
                @endif

                {{-- STUDENT --}}
                @if ($roleStr === 'student')
                    <a href="{{ $isVerificationPage ? '#' : ($streamId ? route('student.class.show', ['streamId' => $streamId]) : route('student.dashboard')) }}"
                      class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $isStudDash ? $activeLink : $idleLink }} {{ $isVerificationPage ? 'cursor-not-allowed opacity-50' : '' }}"
                      @if($isVerificationPage) onclick="event.preventDefault()" @endif>
                       <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 10.5V21h14v-10.5"/>
                       </svg>
                       <span class="{{ $labelCls }}">Dashboard</span>
                   </a>

                    <a href="{{ $isVerificationPage ? '#' : route('student.calendar') }}"
                      class="flex items-center gap-3 rounded-lg px-3 py-2 {{ $isStudCal ? $activeLink : $idleLink }} {{ $isVerificationPage ? 'cursor-not-allowed opacity-50' : '' }}"
                      @if($isVerificationPage) onclick="event.preventDefault()" @endif>
                      <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                       <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v3M8 2v3M3 8h18"/>
                       <rect x="3" y="5" width="18" height="16" rx="2" ry="2" />
                       <path stroke-linecap="round" stroke-linejoin="round" d="M7 12h4m2 0h4m-10 4h4"/>
                     </svg>
                       <span class="{{ $labelCls }}">Calendar</span>
                    </a>
                @endif
            </nav>
        @endauth
    </div>

    {{-- =========================
         B) RAIL MENU (CLOSED)
       ========================= --}}
    <div x-show="!open" x-cloak class="flex-1 overflow-y-auto">
        <nav class="mt-6 flex flex-col items-center gap-4">

            {{-- SUPER ADMIN rail --}}
            @if ($isSuper)
                <a href="{{ RouteFacade::has('manage.pending-admins') ? route('manage.pending-admins') : (RouteFacade::has('admin.users.pending') ? route('admin.users.pending') : '#') }}"
                   title="Pending Admin Approvals"
                   class="{{ $pill }} {{ $isSuperApprovals ? $activePil : $idlePil }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-1a4 4 0 0 0-4-4h0a4 4 0 0 0-4 4v1"/>
                        <circle cx="12" cy="10" r="3.5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 8v6m3-3h-6"/>
                    </svg>
                    @if ($pendingAdminsToApprove > 0)
                        <span class="absolute -right-1 -top-1 min-w-[18px] h-[18px] px-1.5 inline-flex items-center justify-center text-[10px] leading-[18px] font-semibold rounded-full bg-red-600 text-white shadow">
                            {{ $pendingAdminsToApprove }}
                        </span>
                    @endif
                </a>
            @endif

            {{-- ADMIN rail --}}
            @if ($showAdmin)
                <a href="{{ route('dashboard') }}" title="Dashboard"
                   class="{{ $pill }} {{ $isAdminDash ? $activePil : $idlePil }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 10.5V21h14v-10.5"/>
                    </svg>
                </a>

                <a href="{{ route('calendar') }}" title="Calendar"
                   class="{{ $pill }} {{ $isAdminCal ? $activePil : $idlePil }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v3M8 2v3M3 8h18"/>
                        <rect x="3" y="5" width="18" height="16" rx="2" ry="2" />
                    </svg>
                </a>

                <a href="{{ route('coordinator') }}" title="Coordinator"
                   class="{{ $pill }} {{ $isAdminCoord ? $activePil : $idlePil }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-1a4 4 0 0 0-4-4h0a4 4 0 0 0-4 4v1"/>
                        <circle cx="12" cy="10" r="3.5"/>
                    </svg>
                </a>

                <a href="{{ route('admin.coordinators.hub') }}" title="Coordinator Hub"
                   class="{{ $pill }} {{ $isAdminHub ? $activePil : $idlePil }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="8" height="8" rx="2"/>
                        <rect x="13" y="3" width="8" height="8" rx="2"/>
                        <rect x="3" y="13" width="8" height="8" rx="2"/>
                        <rect x="13" y="13" width="8" height="8" rx="2"/>
                    </svg>
                </a>
            @endif

            {{-- COORDINATOR rail --}}
            @if ($roleStr === 'coordinator')
                <a href="{{ route('coordinator.dashboard', ['token' => $token]) }}" title="Dashboard"
                   class="{{ $pill }} {{ $isCoordDash ? $activePil : $idlePil }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 10.5V21h14v-10.5"/>
                    </svg>
                </a>

                <a href="{{ route('coordinator.calendar') }}" title="Calendar"
                   class="{{ $pill }} {{ $isCoordCal ? $activePil : $idlePil }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v3M8 2v3M3 8h18"/>
                        <rect x="3" y="5" width="18" height="16" rx="2" ry="2" />
                    </svg>
                </a>

                @if ($streamId)
                    <a href="{{ route('coordinator.verifications', ['streamId' => $streamId]) }}" title="Pending Students"
                       class="{{ $pill }} {{ $isCoordPend ? $activePil : $idlePil }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-1a4 4 0 0 0-4-4h0a4 4 0 0 0-4 4v1"/>
                            <circle cx="12" cy="10" r="3.5"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 8v6m3-3h-6"/>
                        </svg>
                    </a>

                    {{-- ⭐ NEW: Archived Sections (rail) --}}
                    <a href="{{ route('coordinator.sections.archived', ['stream' => $streamId]) }}" title="Archived Sections"
                       class="{{ $pill }} {{ $isCoordArchived ? $activePil : $idlePil }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18l-2 12H5L3 7z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2"/>
                        </svg>
                    </a>
                @endif
            @endif

            {{-- STUDENT rail --}}
            @if ($roleStr === 'student')
                <a href="{{ $streamId ? route('student.class.show', ['streamId' => $streamId]) : route('student.dashboard') }}"
                   title="Dashboard"
                   class="{{ $pill }} {{ $isStudDash ? $activePil : $idlePil }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 10.5V21h14v-10.5"/>
                    </svg>
                </a>

                <a href="{{ route('student.calendar') }}" title="Calendar"
                   class="{{ $pill }} {{ $isStudCal ? $activePil : $idlePil }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconCls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v3M8 2v3M3 8h18"/>
                        <rect x="3" y="5" width="18" height="16" rx="2" ry="2" />
                    </svg>
                </a>
            @endif
        </nav>
    </div>

    {{-- ===== Compact Profile (bottom) ===== --}}
    @auth
        <div class="mt-auto px-3 py-3">
            {{-- closed state --}}
            <div x-show="!open" x-cloak class="flex justify-center">
                <a href="{{ route('settings.profile') }}"
                   title="{{ auth()->user()->name }}"
                   class="h-10 w-10 rounded-xl bg-neutral-200 dark:bg-neutral-700 flex items-center justify-center text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ auth()->user()->initials() }}
                </a>
            </div>

            {{-- open state --}}
            <div x-show="open" x-cloak>
                <flux:dropdown position="bottom" align="start">
                    <flux:profile
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()
                        "
                        icon-trailing="chevrons-up-down"
                    />
                    <flux:menu class="w-[220px]">
                        <flux:menu.item :href="route('settings.profile')" icon="cog">
                            Settings
                        </flux:menu.item>
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                Log Out
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>
    @endauth
</flux:sidebar>
