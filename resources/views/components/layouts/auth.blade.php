<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>Career Services | Login</title>

    <!-- ✅ Preload background image to reduce flicker -->
    <link rel="preload" as="image" href="{{ asset('dhvsu.jpg') }}">

    <!-- ✅ Vite + Tailwind + Livewire -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <!-- ✅ Fade-in page fix (prevents white flash) -->
    <style>
        body {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        body.loaded {
            opacity: 1;
        }
    </style>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('loaded');
        });
    </script>
</head>

<body class="min-h-screen flex items-center justify-center"
      style="background: url('{{ asset('dhvsu.jpg') }}') no-repeat center center; background-size: cover;">
    
    <div class="w-full max-w-md">
        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>
