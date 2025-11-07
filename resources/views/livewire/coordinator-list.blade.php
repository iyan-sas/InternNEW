@php
    use App\Enums\UserRole;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Carbon;

    // Normalize: works whether $coordinators is an array or a Collection
    $items = collect($coordinators ?? []);

    // Card colors
    $colors = [
        ['bg' => '#DBEAFE', 'border' => 'border-blue-400',  'text' => 'text-blue-900'],
        ['bg' => '#BBF7D0', 'border' => 'border-green-400', 'text' => 'text-green-900'],
        ['bg' => '#FEF9C3', 'border' => 'border-yellow-400','text' => 'text-yellow-900'],
        ['bg' => '#FBCFE8', 'border' => 'border-pink-400',  'text' => 'text-pink-900'],
        ['bg' => '#E9D5FF', 'border' => 'border-purple-400','text' => 'text-purple-900'],
        ['bg' => '#FECACA', 'border' => 'border-red-400',   'text' => 'text-red-900'],
    ];
@endphp

<div class="space-y-4">

    {{-- 🔴 Red/green flash messages --}}
    @if (session('success'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 2000)"
            x-show="show"
            x-transition
            class="text-green-700 bg-white border border-green-400 px-4 py-2 rounded-md"
        >
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="text-red-700 bg-white border border-red-400 px-4 py-2 rounded-md">
            {{ session('error') }}
        </div>
    @endif

    {{-- ✅ Coordinator List (Approved + Online only) --}}
    @if ($items->isEmpty())
        <p class="text-gray-600">No coordinators online.</p>
    @else
        <div class="flex flex-col gap-4">
            @foreach ($items as $index => $coordinator)
                @php
                    $style = $colors[$index % count($colors)];
                    $id    = Arr::get($coordinator, 'id');
                    $name  = Arr::get($coordinator, 'name');
                    $email = Arr::get($coordinator, 'email');

                    // last_seen_at might come as string from Livewire serialization
                    $lastSeenRaw = Arr::get($coordinator, 'last_seen_at');
                    $lastSeen    = $lastSeenRaw ? \Illuminate\Support\Carbon::parse($lastSeenRaw) : null;
                @endphp

                <div
                    class="flex items-center justify-between p-4 rounded-xl border-2 {{ $style['border'] }} shadow-sm"
                    style="background-color: {{ $style['bg'] }};"
                >
                    <div class="{{ $style['text'] }}">
                        <p class="font-semibold text-lg">{{ $name }}</p>
                        <p class="text-sm">{{ $email }}</p>
                        @if ($onlyOnline)
                            <p class="text-xs text-gray-600 mt-1">
                                Last seen: {{ $lastSeen ? $lastSeen->diffForHumans() : '—' }}
                            </p>
                        @endif
                    </div>

                    {{-- Show delete only to Admins (optional) --}}
                    @if (auth()->check() && auth()->user()->role === UserRole::Admin)
                        <button
                            wire:click="deleteCoordinator({{ (int) $id }})"
                            wire:loading.attr="disabled"
                            class="text-red-600 hover:text-red-800 font-semibold"
                        >
                            Delete
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

</div>
