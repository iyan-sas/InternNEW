<x-layouts.app :title="__('Admin Dashboard')">
    {{-- Hero Header Section --}}
    <div class="relative isolate mb-6 w-full bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 px-3 sm:px-6 lg:px-8 py-4 sm:py-5 rounded-xl shadow-sm overflow-visible">
        {{-- Decorative background pattern (behind everything) --}}
        <div class="absolute inset-0 z-0 opacity-5 pointer-events-none">
            <div class="absolute inset-0"
                 style="background-image: radial-gradient(circle at 2px 2px, rgb(37, 99, 235) 1px, transparent 0); background-size: 32px 32px;">
            </div>
        </div>

        <div class="relative z-10">
            {{-- Responsive grid: stacks on mobile, 2 cols on lg --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 items-start">
                {{-- Left: Title + Subheading --}}
                <div class="space-y-1.5 sm:space-y-2">
                    <flux:heading
                        size="xl"
                        level="1"
                        class="text-[22px] sm:text-3xl lg:text-4xl font-bold leading-tight text-blue-900"
                    >
                        {{ __('Admin Dashboard') }}
                    </flux:heading>

                    <flux:subheading
                        size="lg"
                        class="text-[13px] sm:text-base text-blue-700/80 leading-relaxed"
                    >
                        {{ __('Manage the Coordinator modules data from here') }}
                    </flux:subheading>

                    {{-- Quick Stats Pills --}}
                    <div class="flex flex-wrap gap-2 pt-1.5 sm:pt-2">
                        <span class="inline-flex items-center px-2.5 sm:px-3 py-1 rounded-full text-[11px] sm:text-xs font-medium bg-blue-200 text-blue-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                            </svg>
                            <span class="truncate max-w-[60vw] sm:max-w-none">{{ auth()->user()->email }}</span>
                        </span>

                        <span class="inline-flex items-center px-2.5 sm:px-3 py-1 rounded-full text-[11px] sm:text-xs font-medium bg-green-200 text-green-800">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-1 animate-pulse"></span>
                            {{ __('Active') }}
                        </span>
                    </div>
                </div>

                {{-- Right: Welcome chip (solid bg + responsive width) --}}
                <div class="justify-self-stretch lg:justify-self-end self-start w-full lg:w-auto relative z-[9999]">
                    @if (session('welcome_admin'))
                        <div id="welcome-admin-banner" class="transition-all duration-500 ease-in-out opacity-100 translate-y-0">
                            <div
                                class="rounded-xl border border-blue-300 bg-blue-600 text-white shadow-lg ring-1 ring-blue-300
                                       px-3 sm:px-4 lg:px-6 py-3 sm:py-3.5 max-w-full w-full sm:w-auto lg:max-w-md"
                                style="backdrop-filter:saturate(140%) blur(1px);"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-white/20 flex items-center justify-center">
                                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm sm:text-base lg:text-lg font-bold truncate">
                                            {{ __('Welcome back,') }} {{ auth()->user()->name ?? 'Admin' }}!
                                        </div>
                                        <div class="text-xs sm:text-sm opacity-95">
                                            {{ __('You\'re now signed in.') }} • {{ now()->format('M d, Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Auto-hide after 3s (no Alpine) --}}
                        <script>
                            (function(){
                                const el = document.getElementById('welcome-admin-banner');
                                if (!el) return;
                                setTimeout(() => {
                                    el.style.opacity = '0';
                                    el.style.transform = 'translateY(-8px)';
                                    setTimeout(() => el.remove(), 500);
                                }, 3000);
                            })();
                        </script>
                    @endif
                </div>
            </div>

            <flux:separator variant="subtle" class="mt-4 opacity-30" />

            {{-- Success Messages --}}
            @if (session('success'))
                <div class="mt-4 p-3 sm:p-4 bg-green-50 border border-green-200 rounded-lg">
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

    {{-- Pending Approvals Widget (System Creator Only) --}}
    @if (auth()->user()?->is_creator)
        <div class="mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <livewire:admin.pending-widget />
            </div>
        </div>
    @endif

    {{-- Charts Component --}}
    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @livewire(\App\Livewire\Analytics\OjtCharts::class)
    </div>

    {{-- Main Dashboard Content --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 sm:p-6 lg:p-8">
        @livewire('dashboard')
    </div>

    {{-- Prevent FOUC --}}
    <style>
        [x-cloak]{display:none!important}
        html{scroll-behavior:smooth}
        @media (min-width:1024px){
            ::-webkit-scrollbar{width:8px;height:8px}
            ::-webkit-scrollbar-track{background:#f1f5f9}
            ::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:4px}
            ::-webkit-scrollbar-thumb:hover{background:#94a3b8}
        }
    </style>
</x-layouts.app>
