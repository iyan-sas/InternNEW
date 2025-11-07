<x-layouts.app title="Student Sign in" :hideSidebar="true">
    <div class="relative min-h-[92vh] flex items-center justify-center px-4">
        <div class="relative z-10 w-full max-w-md">
            {{-- Card with solid blue border and blue shadow --}}
            <div class="rounded-2xl border-2 border-blue-500 bg-blue-200 ring-5 ring-blue-500 hover:ring-blue-700 transition">
                <div class="px-6 pt-7 pb-3 text-center">
                    <img
                        src="{{ asset('career_services.png') }}"
                        alt="Career Services"
                        class="mx-auto h-14 w-14 object-contain"
                        loading="lazy"
                    />

                    <h1 class="mt-3 text-xl font-semibold text-zinc-900">
                        Sign in as Student
                    </h1>

                    <p class="mt-2 text-sm text-zinc-700">
                        Only <span class="font-medium">{{ $allowed ?? 'your campus' }}</span> email addresses are allowed.
                    </p>
                </div>

                {{-- Alert --}}
                @if (session('error'))
                    <div class="mx-6 mt-3 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Form --}}
                <form method="GET" action="{{ route('google.login') }}" class="px-6 pt-4 pb-6 space-y-4">
                    @if (!empty($token))
                        <input type="hidden" name="token" value="{{ $token }}">
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-zinc-800">
                            Campus Email
                        </label>

                        <div class="mt-1 relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3 grid place-items-center">
                                <svg class="h-4 w-4 text-zinc-400" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20 4H4a2 2 0 00-2 2v1l10 6 10-6V6a2 2 0 00-2-2zm0 6l-8 4.8L4 10v8a2 2 0 002 2h12a2 2 0 002-2v-8z"/>
                                </svg>
                            </span>
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                placeholder="you@pampangastateu.edu.ph"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-10 py-2.5 text-zinc-900 placeholder-zinc-400 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            />
                        </div>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="group w-full inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 py-2.5 font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 active:scale-[.99] transition">
                        <svg viewBox="0 0 48 48" class="h-5 w-5" aria-hidden="true">
                            <path fill="#FFC107" d="M43.6 20.5H42v-.1H24v7.2h11.3C33.4 31 29.1 34 24 34c-6.6 0-12-5.4-12-12s5.4-12 12-12c3 0 5.7 1.1 7.8 3l5-5C33.6 4.1 29.1 2 24 2 12.9 2 4 10.9 4 22s8.9 20 20 20 18.7-8.5 18.7-19.1c0-1.3-.1-2.2-.1-2.4z"/>
                            <path fill="#FF3D00" d="M6.3 14.7l5.9 4.3C13.9 14.9 18.5 12 24 12c3 0 5.7 1.1 7.8 3l5-5C33.6 4.1 29.1 2 24 2 15.6 2 8.5 6.8 6.3 14.7z"/>
                            <path fill="#4CAF50" d="M24 42c5 0 9.6-1.9 13-5.1l-6-4.9C29.1 34 26.7 35 24 35c-5 0-9.2-3.2-10.7-7.6l-6 4.6C9.4 38.7 16.1 42 24 42z"/>
                            <path fill="#1976D2" d="M43.6 20.5H42v-.1H24v7.2h11.3c-1.1 3.2-4.5 7.4-11.3 7.4-5 0-9.2-3.2-10.7-7.6l-6 4.6C9.4 38.7 16.1 42 24 42c8.8 0 16.3-6 18.3-14.5.4-1.7.6-3.5.6-5.4 0-1.3-.1-2.2-.1-2.4z"/>
                        </svg>
                        Continue with Google
                    </button>

                    <p class="text-[13px] text-zinc-600 text-center leading-snug">
                     Use your campus email (e.g., <span class="font-large">you@pampangastateu.edu.ph</span>).
                    </p>

                </form>
            </div>

            <p class="mt-4 text-center text-xs text-zinc-600">
                Career Services · Pampanga State University
            </p>
        </div>
    </div>
</x-layouts.app>
