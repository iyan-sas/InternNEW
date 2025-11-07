<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Models\Stream;
use App\Models\Section;
use App\Models\User;
use App\Models\AdminDocument;
use App\Models\Event;

use Livewire\Volt\Volt;
use App\Livewire\Admin\PendingUsers;
use App\Livewire\Admin\CoordinatorHub;

// NEW: archived sections Livewire page for coordinators
use App\Livewire\Coordinator\ArchivedSections;

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentUploadController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\StudentCalendarController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CoordinatorDashboardController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SectionStudentController;
use App\Http\Controllers\StudentVerificationController;
use App\Http\Controllers\StudentVerificationFileController; // ✅ keep using your existing controller
use App\Livewire\Student\VerifyUploads;
use App\Livewire\Coordinator\VerificationQueue;

/*
|--------------------------------------------------------------------------
| Local-only debug (remove in prod)
|--------------------------------------------------------------------------
*/
if (app()->environment('local')) {
    Route::get('/_phpinfo', fn () => phpinfo())->name('_phpinfo');
    Route::get('/_env', fn () => response()->json([
        'APP_URL'             => config('app.url'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size'       => ini_get('post_max_size'),
        'upload_tmp_dir'      => ini_get('upload_tmp_dir'),
        'memory_limit'        => ini_get('memory_limit'),
    ]));
}

/*
|--------------------------------------------------------------------------
| Public: Register & Home
|--------------------------------------------------------------------------
*/
Route::view('/register', 'livewire.auth.register')->name('register');
Route::get('/', fn () => redirect()->route('login'))->name('home');

/*
|--------------------------------------------------------------------------
| Google Login (OAuth endpoints)
|--------------------------------------------------------------------------
*/
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('google.callback');
Route::get('/auth/google/student', [GoogleController::class, 'studentDomainGate'])->name('google.student.gate');

/*
|--------------------------------------------------------------------------
| ✅ PUBLIC: token route handled by CONTROLLER (fixes 404)
|    Uses name: student.verify.token
|--------------------------------------------------------------------------
*/
Route::get(
    '/student/verify-token/{token}',
    [StudentVerificationController::class, 'verifyToken']
)->name('student.verify.token');

/*
|--------------------------------------------------------------------------
| Public entry links
|--------------------------------------------------------------------------
*/
// Legacy coordinator invite → OAuth → coordinator dashboard
Route::get('/class/{token}', function (string $token) {
    $stream = Stream::where('invite_token', $token)->firstOrFail();

    session([
        'redirect_after_login'          => route('coordinator.dashboard', ['token' => $token]),
        'login_role'                    => 'coordinator',
        'stream_token'                  => $token,
        'pending_coordinator_stream_id' => $stream->id,
        'pending_owner_admin_id'        => $stream->created_by ?? $stream->admin_id,
    ]);

    return redirect()->route('google.login');
})->name('class.login');

// New coordinator JOIN → OAuth → WAITING PAGE (approval required)
Route::get('/coordinator-join/{token}', function (string $token) {
    $stream = Stream::where('coordinator_invite_token', $token)->first();

    if (! $stream) {
        $section = Section::where('coordinator_token', $token)->firstOrFail();
        $stream  = $section->stream;
    }

    session([
        'redirect_after_login'          => route('waiting-approval'),
        'login_role'                    => 'coordinator',
        'stream_token'                  => $stream->invite_token ?? null,
        'pending_coordinator_stream_id' => $stream->id,
        'pending_owner_admin_id'        => $stream->created_by ?? $stream->admin_id,
    ]);

    return redirect()->route('google.login');
})->name('coordinator.join');

/*
|--------------------------------------------------------------------------
| Section-specific student join with verification flow
|  🔧 Persist stream_id & section_id in session, and pass ?section= when logged in.
|--------------------------------------------------------------------------
*/
Route::get('/section-join/{token}', function (string $token) {
    $section = Section::where('student_upload_token', $token)->firstOrFail();
    $stream  = $section->stream;

    session([
        'stream_id'                  => $stream->id,
        'section_id'                 => $section->id,
        'student_token'              => $stream->student_token,
        'stream_token'               => $stream->student_token,
        'redirect_after_login_token' => $token,
        'login_role'                 => 'student',
    ]);

    return auth()->check()
        ? redirect()->route('student.verify.token', [
            'token'   => $stream->student_token,
            'section' => $section->id,
        ])
        : redirect()->route('google.student.gate', ['token' => $token]);
})->name('section.student.join');

/*
|--------------------------------------------------------------------------
| Student Upload Routes
|--------------------------------------------------------------------------
*/
Route::get('/student-upload/{token}', [StudentUploadController::class, 'showUploadForm'])
    ->name('student.upload');
Route::post('/student-upload/{token}', [StudentUploadController::class, 'submitUpload'])
    ->name('student.upload.submit');

/*
|--------------------------------------------------------------------------
| Waiting Approval Page (for coordinators)
|--------------------------------------------------------------------------
*/
Route::get('/waiting-approval', function () {
    $user = auth()->user();
    abort_unless($user, 403);

    $isApproved = method_exists($user, 'isApproved')
        ? $user->isApproved()
        : (bool) ($user->is_approved ?? false);

    if ($isApproved) {
        return session('stream_token')
            ? redirect()->route('coordinator.dashboard', ['token' => session('stream_token')])
            : redirect()->route('dashboard');
    }

    return view('livewire.auth.waiting-approval');
})->middleware(['auth','verified','touch.seen'])->name('waiting-approval');

/*
|--------------------------------------------------------------------------
| 🔁 ROLE/ABILITY-BASED DASHBOARD REDIRECTOR
|  - If user can approve users (Super Admin), go to Pending Admin Approvals
|  - Else fallback by role strings
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $user = Auth::user();
    if (! $user) {
        return redirect()->route('login');
    }

    // ✅ Ability-based first (works even if role is blank)
    if ($user->can('approve-users')) {
        return redirect()->route('manage.pending-admins');
    }

    // Fallback by role string / enum
    $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

    return match ($role) {
        'admin'       => redirect()->route('admin.dashboard'),
        'coordinator' => redirect()->route('coordinator.dashboard'),
        default       => redirect()->route('student.dashboard'),
    };
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Admin Dashboard (bounce anyone who can approve users)
    Route::get('/admin/dashboard', function () {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login');
        }

        // ✅ Super Admins (or anyone with this Gate) go to approvals page
        if ($user->can('approve-users')) {
            return redirect()->route('manage.pending-admins');
        }

        return view('dashboard');
    })->middleware(['verified'])->name('admin.dashboard');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');

    // Coordinator area
    Route::get('/coordinator', function () {
        $coordinators = User::where('role', 'coordinator')
            ->where('owner_admin_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('classroom.coordinator', compact('coordinators'));
    })->name('coordinator');

    Route::get('/coordinator/dashboard', [CoordinatorDashboardController::class, 'index'])
        ->middleware(['coord.approved','touch.seen'])->name('coordinator.dashboard');

    Route::get('/coordinator/calendar', fn () =>
        session('stream_token') ? view('coordinator.calendar')
                                : redirect()->route('coordinator.dashboard')
    )->middleware(['coord.approved','touch.seen'])->name('coordinator.calendar');

    // Coordinator self-create page → must be APPROVED
    Route::get('/coordinator/self-create/{token}', function (string $token) {
        return view()->first(
            ['coordinator.self-create', 'livewire.coordinator.self-create'],
            ['token' => $token]
        );
    })->middleware(['coord.approved','touch.seen'])->name('coordinator.self-create');

    // Classes / Sections
    Route::get('/class/calendar', [ClassroomController::class, 'coordinatorCalendar'])->name('class.calendar');
    Route::get('/class/show/{token}', [ClassroomController::class, 'show'])->name('class.show');

    // Section page
    Route::get('/class/{stream}/section/{section}', [SectionController::class, 'show'])
        ->middleware('verified')->name('section.show');

    /*
    |--------------------------------------------------------------------------
    | STUDENT VERIFICATION (existing Livewire by streamId — kept)
    |--------------------------------------------------------------------------
    */
    Route::get('/student/verify/{streamId}', VerifyUploads::class)->name('student.verify.show');

    // ✅ Files preview routes
    Route::get('/student-verification/{stream}/view/{type}', [StudentVerificationFileController::class, 'show'])
        ->whereIn('type', ['cor','id'])
        ->name('student.verification.view');

    Route::get('/verification/file/{verification}/{type}', [StudentVerificationFileController::class, 'showByVerification'])
        ->whereIn('type', ['cor','id'])
        ->name('verification.file');

    // Status check API
    Route::get('/student/verification/check-status', [StudentVerificationController::class, 'checkStatus'])
        ->name('student.verification.check-status');

    /*
    |--------------------------------------------------------------------------
    | Student area (APPROVED ONLY)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['student.approved'])->group(function () {
        Route::get('/student-class/show/{streamId}', [StudentUploadController::class, 'showById'])
            ->name('student.class.show');

        Route::get('/student/verification', [StudentVerificationController::class, 'index'])
            ->name('student.verification.index');
    });

    // Coordinator review queue
    Route::get('/coordinator/verification/{streamId}', VerificationQueue::class)
        ->middleware('coord.approved')->name('coordinator.verifications');

    /*
    |--------------------------------------------------------------------------
    | Coordinator Approval Endpoints (for auto-redirect system)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['coord.approved'])->group(function () {
        Route::get('/coordinator/pending-verifications/{streamId?}', [CoordinatorDashboardController::class, 'getPendingVerifications'])
            ->name('coordinator.pending-verifications');

        Route::post('/coordinator/approve-verification', [CoordinatorDashboardController::class, 'approveVerification'])
            ->name('coordinator.approve-verification');

        Route::post('/coordinator/reject-verification', [CoordinatorDashboardController::class, 'rejectVerification'])
            ->name('coordinator.reject-verification');

        // 🌟 NEW: Archived Sections page (grouped by School Year)
        Route::get(
            '/coordinator/sections/archives/{stream}',
            ArchivedSections::class
        )->name('coordinator.sections.archived');
    });

    /*
    |--------------------------------------------------------------------------
    | Student domain-protected pages (after auth)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['student.domain'])->group(function () {
        // ✅ Dashboard decides here
        Route::get('/student/dashboard', function () {
            $user     = auth()->user();
            $streamId = session('stream_id');
            $token    = session('student_token') ?? session('stream_token');

            abort_if(!$user || (!$streamId && !$token), 404);

            if ($streamId) {
                $status = \App\Models\StudentVerification::where('user_id', $user->id)
                    ->where('stream_id', $streamId)
                    ->value('status');

                if ($status === 'approved') {
                    return redirect()->route('student.class.show', ['streamId' => $streamId]);
                }
            }

            return $token
                ? redirect()->route('student.verify.token', ['token' => $token])
                : abort(404);
        })->name('student.dashboard');

        // Legacy/Sidebar compatibility → forwards to public token verify
        Route::get('/students/upload/{token?}', function (?string $token = null) {
            $useToken = $token ?? session('student_token') ?? session('stream_token');
            abort_if(!$useToken, 404);
            return redirect()->route('student.verify.token', ['token' => $useToken]);
        })->name('students.upload');

        Route::get('/student-class/token/{token}', fn (string $token) =>
            redirect()->route('student.verify.token', ['token' => $token])
        )->name('student-class.token');

        Route::post('/student/{token}/submit', [StudentUploadController::class, 'submitForm'])
            ->name('student.upload.submit');

        Route::get('/student/calendar', fn () =>
            session('stream_token')
                ? app(StudentCalendarController::class)->index()
                : redirect()->route('student.dashboard')
        )->name('student.calendar');
    });

    /*
    |--------------------------------------------------------------------------
    | Notifications & Events
    |--------------------------------------------------------------------------
    */
    Route::get('/notifications/read-all', [NotificationController::class, 'readAll'])
        ->name('notifications.read-all');

    Route::get('/calendar/events', fn () => Event::with('user')
        ->visibleTo(auth()->user())
        ->orderBy('date')
        ->get()
        ->map(fn ($e) => [
            'id'    => (string) $e->id,
            'title' => $e->title,
            'start' => optional($e->date)->timezone(config('app.timezone','UTC'))?->toIso8601String(),
            'allDay'=> false,
            'extendedProps' => [
                'description'  => $e->description,
                'audience'     => $e->audience,
                'posted_by'    => $e->user?->name,
                'posted_role'  => strtolower((string)($e->user?->role->value ?? $e->user?->role)),
                'can_update'   => auth()->id() === $e->user_id,
                'can_delete'   => auth()->id() === $e->user_id,
            ],
        ])->values()
    )->name('calendar.events');

    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::patch('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

    /*
    |--------------------------------------------------------------------------
    | Students (Remove from section)
    |--------------------------------------------------------------------------
    */
    Route::delete('/students/{id}/remove', [StudentUploadController::class, 'destroy'])
        ->name('students.remove');

    // Section-aware remove for Coordinator → Students tab
    Route::delete('/sections/{section}/students/{user}', [SectionStudentController::class, 'destroy'])
        ->name('sections.students.destroy');

    // Test upload
    Route::get('/test-upload', \App\Livewire\FileTestUpload::class);

    // Volt Settings
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // AdminDocument file streaming
    Route::get('/files/{doc}', fn (AdminDocument $doc) =>
        response()->file(storage_path('app/public/'.$doc->filename))
    )->name('files.show');

    // Submissions secure streaming
    Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])
        ->name('submissions.show');
});

/*
|--------------------------------------------------------------------------
| Admin-only & SuperAdmin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','verified','touch.seen'])->group(function () {
    Route::get('/admin/coordinators', CoordinatorHub::class)->name('admin.coordinators.hub');
});

Route::middleware(['auth','can:approve-users'])->group(function () {
    Route::get('/admin/users/pending', PendingUsers::class)->name('admin.users.pending');

    Route::post('/admin/users/{id}/approve', function (int $id) {
        $user = User::findOrFail($id);

        $ok = $user->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        if ($ok) {
            return redirect()
                ->route('admin.users.pending')
                ->with('success', 'Admin approved successfully!');
        }

        return back()->with('error', 'Approval failed. Please try again.');
    })->name('admin.users.approve');
});

/*
|--------------------------------------------------------------------------
| Super Admin ONLY — minimal approvals page
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','can:approve-users'])->group(function () {
    Route::get('/manage/pending-admins', PendingUsers::class)->name('manage.pending-admins');
    // 🔹 Alias name so both old and new route names work (prevents blank first render)
    Route::get('/pending-admins', fn () => redirect()->route('manage.pending-admins'))
        ->name('pending-admins');
});

/*
|--------------------------------------------------------------------------
| Approved Admins ONLY placeholder group
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','admin.approved'])->group(function () {
    // Place additional admin-only (approved) routes here in the future.
});

/*
|--------------------------------------------------------------------------
| Unapproved Admin holding page
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->get('/admin/awaiting-approval', function () {
    return view()->first(['admin.awaiting', 'livewire.admin.awaiting'], []);
})->name('admin.awaiting-approval');

/*
|--------------------------------------------------------------------------
| Laravel Auth Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
