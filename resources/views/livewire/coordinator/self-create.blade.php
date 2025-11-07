{{-- resources/views/coordinator/self-create.blade.php --}}
@php
    // Accept token from route OR fall back to any token already in session
    $token = $token ?? session('stream_token');

    // Keep the token in session for downstream routes (dashboard, etc.)
    if (!empty($token)) {
        session(['stream_token' => $token]);
    }
@endphp

<x-layouts.app :title="'Coordinator — Create Your Section'">
    <style>[x-cloak]{display:none!important}</style>

    <div class="max-w-6xl mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="mb-4">
            <h1 class="text-2xl font-semibold text-slate-900">Create Your Section</h1>
            <p class="text-slate-600 text-sm mt-1">
                You’re signed in as a coordinator. Use the form below to create your section for this class.
            </p>

            @if(!empty($token))
                <div class="mt-2 text-xs text-slate-500">
                    <span class="font-medium">Class token:</span>
                    <span class="select-all break-all">{{ $token }}</span>
                </div>
            @else
                <div class="mt-2 rounded border border-amber-300 bg-amber-50 text-amber-800 text-xs px-3 py-2">
                    No class token detected. If you opened this page directly, please use your
                    <span class="font-medium">coordinator-join</span> link.
                </div>
            @endif
        </div>

        {{-- Livewire: Self-create section form --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4">
            {{-- Component alias must match your Livewire class: App\Livewire\Coordinator\SelfCreateSection --}}
            <livewire:coordinator.self-create-section :token="$token" />
        </div>

        {{-- Quick link back to Coordinator Dashboard --}}
        <div class="mt-6">
            <a href="{{ route('coordinator.dashboard', ['token' => session('stream_token')]) }}"
               class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
               Go to Coordinator Dashboard
            </a>
            <p class="text-xs text-slate-500 mt-2">
                On the dashboard, open the <span class="font-medium">Sections</span> panel to manage your section(s).
            </p>
        </div>
    </div>
</x-layouts.app>
