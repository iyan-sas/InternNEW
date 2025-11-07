<div x-data x-on:ojt:view.window="$wire.openFor($event.detail)">
    @if ($open)
        <!-- overlay (very high z so it sits above the sidebar) -->
        <div class="fixed inset-0 z-[9998] bg-black/40" wire:click="close"></div>

        <!-- modal container -->
        <div class="fixed inset-0 z-[9999] flex items-center justify-center p-2 sm:p-4" role="dialog" aria-modal="true" aria-labelledby="ojt-info-title">
            <div
                class="bg-white rounded-2xl border shadow-xl overflow-hidden
                       w-[min(24rem,calc(100vw-10rem))] sm:w-full sm:max-w-lg"
            >
                <!-- header -->
                <div class="flex items-center justify-between border-b px-4 sm:px-6 py-3 sm:py-4 sticky top-0 bg-white">
                    <h3 id="ojt-info-title" class="text-base sm:text-lg font-semibold">OJT Information</h3>
                    <button class="text-gray-500 hover:text-gray-700 text-2xl leading-none" wire:click="close" aria-label="Close">&times;</button>
                </div>

                <!-- body: scrolls INSIDE the panel, not the page -->
                <div class="px-4 sm:px-6 py-4 sm:py-5 text-sm leading-relaxed text-gray-800 space-y-5 overflow-y-auto max-h-[70vh] sm:max-h-[75vh]">
                    <!-- basic info -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-2">
                        <p>
                            <span class="font-medium text-gray-900">Full Name:</span>
                            <span class="ml-1 break-words">{{ $fullName }}</span>
                        </p>

                        <!-- (removed Student No) -->

                        <p class="sm:col-span-2">
                            <span class="font-medium text-gray-900">Email:</span>
                            <span class="ml-1 align-baseline break-all sm:break-words">{{ $email }}</span>
                        </p>
                    </div>

                    @if ($profile)
                        <div class="border-t pt-4 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-3">
                                <p>
                                    <span class="font-medium text-gray-900">Contact Number:</span>
                                    <span class="ml-1 break-words">{{ $profile->contact }}</span>
                                </p>

                                <p>
                                    <span class="font-medium text-gray-900">Home Address:</span>
                                    <span class="ml-1 break-words">{{ $profile->address }}</span>
                                </p>

                                <p>
                                    <span class="font-medium text-gray-900">Company / HTE Name:</span>
                                    <span class="ml-1 break-words">{{ $profile->company_name }}</span>
                                </p>

                                @php
                                    $raw = (string) ($profile->company_address ?? '');
                                    $parts = preg_split('/,|\r\n|\r|\n/u', $raw) ?: [];
                                    $seen = [];
                                    $uniq = [];
                                    foreach ($parts as $p) {
                                        $clean = trim(preg_replace('/\s+/u', ' ', $p));
                                        $clean = trim($clean, " \t\n\r\0\x0B,.");
                                        if ($clean === '') continue;
                                        $key = mb_strtolower($clean);
                                        if (!isset($seen[$key])) { $seen[$key] = true; $uniq[] = $clean; }
                                    }
                                    $cleanCompanyAddr = implode(', ', $uniq);
                                @endphp

                                <p class="sm:col-span-2">
                                    <span class="font-medium text-gray-900">Company Address:</span>
                                    <span class="ml-1 block break-words">{{ $cleanCompanyAddr }}</span>
                                </p>
                            </div>

                            <p class="text-xs text-gray-500 border-t pt-3">
                                Last updated: {{ optional($profile->updated_at)->diffForHumans() }}
                            </p>
                        </div>
                    @else
                        <div class="border-t pt-4">
                            <p class="italic text-gray-600">
                                This student has not yet filled out their OJT form.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
