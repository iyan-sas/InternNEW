{{-- resources/views/livewire/auth/waiting-approval.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Pending Approval</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- ✅ Auto-refresh every 4s to re-check approval status --}}
    <meta http-equiv="refresh" content="4">

    <style>
        :root { color-scheme: light dark; }
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial;
               background:#f6f7f9; margin:0; display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .card { width: min(560px, 92vw); background:#fff; border:1px solid #e5e7eb; border-radius:14px; padding:28px;
                box-shadow: 0 8px 24px rgba(0,0,0,.06); }
        .title { font-size: 22px; font-weight: 700; margin: 0 0 8px; }
        .muted { color:#6b7280; margin-bottom: 18px; line-height:1.45; }
        .row { display:flex; gap:10px; align-items:center; }
        .btn { appearance:none; border:1px solid #e5e7eb; background:#fff; color:#111827; font-weight:600;
               padding:10px 14px; border-radius:10px; text-decoration:none; cursor:pointer; }
        .btn-primary { background:#2563eb; border-color:#2563eb; color:#fff; }
        .hint { font-size:12px; color:#6b7280; margin-top:10px; }
    </style>
</head>
<body>
    <div class="card">
        <h1 class="title">⏳ Account Pending Approval</h1>
        <p class="muted">
            Your coordinator account is awaiting admin approval. You’ll get access once an admin approves you.
        </p>

        <div class="row">
            <a class="btn" href="{{ route('login') }}">Back to Login</a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-primary">Log out</button>
            </form>
        </div>

        <p class="hint">This page checks your status every few seconds and will redirect automatically once approved.</p>
    </div>
</body>
</html>
