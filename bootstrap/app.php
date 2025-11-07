<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// ⬇️ Imports (your middlewares)
use App\Http\Middleware\AttachCoordinatorFromInvite;
use App\Http\Middleware\EnsureCoordinatorApproved;
use App\Http\Middleware\TouchLastSeen;
use App\Http\Middleware\EnsureStudentEmailDomain;
// ✅ require student approval before entering class
use App\Http\Middleware\EnsureStudentApprovedForClass;
// ✅ NEW: DB-based gate to stop redirecting back to verification after submit
use App\Http\Middleware\StudentLandingGate;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ✅ Route middleware aliases
        $middleware->alias([
            'admin.approved' => \App\Http\Middleware\EnsureAdminApproved::class,
            'coord.approved'    => EnsureCoordinatorApproved::class,
            'touch.seen'        => TouchLastSeen::class,
            'student.domain'    => EnsureStudentEmailDomain::class,
            'student.approved'  => EnsureStudentApprovedForClass::class,
            // ✅ NEW alias: use in routes like ->middleware('student.landing')
            'student.landing'   => StudentLandingGate::class,
        ]);

        // ✅ Append to the "web" stack (kept from your setup)
        $middleware->web(append: [
            AttachCoordinatorFromInvite::class,
            // NOTE: We keep StudentLandingGate as a route alias (not global) so you can
            // apply it only to student pages. If you want it global, uncomment below:
            // StudentLandingGate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
