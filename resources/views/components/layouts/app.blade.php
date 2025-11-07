<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    {{-- Alpine x-cloak helper --}}
    <style>[x-cloak]{display:none!important}</style>
</head>

@php
    // When a page is rendered with: <x-layouts.app :hideSidebar="true">
    // we'll skip the sidebar and remove the left margin.
    // Use strict boolean to avoid truthy values like "1"/"yes" from requests.
    $noSidebar = ($hideSidebar ?? false) === true;
@endphp

<body
    {{-- If there’s no sidebar, Alpine state isn’t needed. Keep it minimal. --}}
    x-data="{{ $noSidebar ? '{}' : '{ open: window.matchMedia(\'(min-width: 1024px)\').matches }' }}"
    {{-- match page bg para mawala ang dark/black strip sa gilid --}}
    class="flex min-h-screen bg-white dark:bg-zinc-900 antialiased overflow-x-hidden"
>

    {{-- Sidebar (drawer) - render only when not hidden --}}
    @unless ($noSidebar)
        <x-layouts.app.sidebar :title="$title ?? null" />
    @endunless

    {{-- Main Content --}}
    <main
        class="flex-1 overflow-y-auto overflow-x-hidden transition-all duration-300"
        @if ($noSidebar)
            style="margin-left:0"
        @else
            x-bind:style="open ? 'margin-left:256px' : 'margin-left:72px'"
        @endif
    >
        <div id="page-content" class="min-h-screen bg-white dark:bg-zinc-900 p-6">
            {{ $slot }}
        </div>
    </main>

    {{-- Scripts --}}
    @livewireScripts
    @fluxScripts
    @stack('scripts')
</body>
</html>
