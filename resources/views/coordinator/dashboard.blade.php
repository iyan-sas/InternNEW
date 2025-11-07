{{-- resources/views/coordinator/dashboard.blade.php --}}
@php
    $campus = $stream->campus ?? $stream->campus_name ?? null;
    $college = $stream->college ?? $stream->college_name ?? ($stream->class_name ?? null);
    $pageTitle = trim(($campus ? $campus.' — ' : '').($college ?: 'Coordinator Dashboard'));
@endphp

<x-layouts.app :title="$pageTitle">
    <style>[x-cloak]{display:none!important}</style>

    {{-- HERO HEADER --}}
    <div class="relative mb-6 w-full bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 px-4 sm:px-6 lg:px-8 py-5 rounded-xl shadow-sm overflow-hidden">
        <div class="absolute inset-0 opacity-5">
            <div class="absolute inset-0"
                 style="background-image: radial-gradient(circle at 2px 2px, rgb(37, 99, 235) 1px, transparent 0); background-size: 32px 32px;">
            </div>
        </div>

        <div class="relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
                {{-- Left: Title + Subheading --}}
                <div class="space-y-2">
                    <flux:heading size="xl" level="1" class="text-2xl sm:text-3xl lg:text-4xl font-bold text-blue-900">
                        {{ __('Coordinator Dashboard') }}
                    </flux:heading>
                    <flux:subheading size="lg" class="text-sm sm:text-base text-blue-700/80">
                        {{ __('Manage the Coordinator modules data from here') }}
                        @if($campus || $college)
                            <span class="block sm:inline font-semibold">
                                • {{ $campus ? $campus.' — ' : '' }}{{ $college }}
                            </span>
                        @endif
                    </flux:subheading>

                    {{-- Pills --}}
                    <div class="flex flex-wrap gap-2 pt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-200 text-blue-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                            </svg>
                            {{ auth()->user()->email }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-200 text-green-800">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-1 animate-pulse"></span>
                            {{ __('Active') }}
                        </span>
                    </div>
                </div>

                {{-- Right: Optional welcome chip --}}
                @if (session('welcome_coordinator'))
                    <div class="justify-self-start lg:justify-self-end self-start w-full lg:w-auto"
                         x-data="{ show:true }" x-init="setTimeout(()=>show=false,3000)"
                         :class="show ? 'opacity-100 translate-y-0 transition-all duration-500' : 'opacity-0 -translate-y-2 transition-all duration-300'"
                         x-cloak>
                        <div class="rounded-xl border border-blue-300 bg-gradient-to-r from-blue-500 to-blue-600 px-4 sm:px-6 py-3 sm:py-4 shadow-lg text-white max-w-full lg:max-w-md">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-white/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 10-6 0 3 3 0 006 0z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-base sm:text-lg lg:text-xl font-bold truncate">
                                        {{ __('Welcome back,') }} {{ auth()->user()->name ?? 'Coordinator' }}!
                                    </div>
                                    <div class="text-xs sm:text-sm opacity-90">
                                        {{ __('You\'re now signed in.') }} • {{ now()->format('M d, Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <flux:separator variant="subtle" class="mt-4 opacity-30" />

            @if (session('success'))
                <div x-data="{ show: true }"
                     x-init="setTimeout(()=>show=false,3000)"
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="mt-4 p-3 sm:p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm sm:text-base text-green-800 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
    {{-- /HERO HEADER --}}

    {{-- Coordinator Sections Panel (create + manage enabled here) --}}
    <livewire:coordinator.sections-panel :stream="$stream" />

    {{-- Extra tools (optional)
    @if(optional($stream->sections)->isNotEmpty())
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4 mt-6">
            <h4 class="font-semibold text-slate-900 mb-3">Class Tools</h4>
            <livewire:announcements-list :stream-id="$stream->id" />
            <div class="mt-6 space-y-4">
                @livewire('task-form', ['stream_id' => $stream->id], key('task-form-'.$stream->id))
                @livewire('task-list', ['stream_id' => $stream->id], key('task-list-'.$stream->id))
            </div>
        </div>
    @endif
    --}}
</x-layouts.app>
