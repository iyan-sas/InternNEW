{{-- resources/views/submissions/preview.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Preview – {{ $filename }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; background: #f9fafb; color: #111; }
        header { background: #2563eb; color: #fff; padding: 12px 16px; font-weight: 600; }
        iframe { width: 100%; height: 90vh; border: none; display: block; }
        .msg { padding: 20px; font-size: 15px; }
        a { color: #2563eb; text-decoration: underline; }
        .warn { color: #b91c1c; font-weight: 600; }
    </style>
</head>
<body>
<header>
    File Preview: {{ $filename }}
</header>

<main>
    @if($isOffice ?? false)
        {{-- Office docs --}}
        @if($isLocal ?? false)
            <div class="msg warn">
                ⚠️ Office files (Word/Excel/PowerPoint) cannot be previewed on localhost.<br>
                <a href="{{ $fileUrl }}" target="_blank">Open file directly</a>
            </div>
        @else
            <iframe src="{{ $officeViewer }}"></iframe>
        @endif

    @elseif(in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), ['pdf','png','jpg','jpeg','gif','webp','txt']))
        {{-- Inline preview for PDFs, images, txt --}}
        <iframe src="{{ $fileUrl }}"></iframe>

    @else
        {{-- Fallback --}}
        <div class="msg">
            No preview available.<br>
            <a href="{{ $fileUrl }}" target="_blank">Open file directly</a>
        </div>
    @endif
</main>
</body>
</html>
