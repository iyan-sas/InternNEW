{{-- resources/views/livewire/admin/coordinator-hub.blade.php --}}
<div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8 py-6 space-y-6" wire:poll.5s>
    {{-- Header --}}
    <div class="flex items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Coordinators</h1>
        @if ($pendingCount > 0)
            <span class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-3 py-1.5 text-sm font-medium text-white shadow">
                <span class="h-1.5 w-1.5 rounded-full bg-white/80"></span>
                {{ $pendingCount }} pending
            </span>
        @endif
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Search --}}
    <div>
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search name/email…"
            class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-zinc-800 placeholder-zinc-400 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
        />
    </div>

    {{-- Signed-in (approved only) --}}
    <section class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="flex items-center justify-between px-4 py-3 sm:px-5 border-b">
            <h2 class="text-sm font-medium text-zinc-700">
                Signed-in Coordinators (last 5 mins)
            </h2>
            <span class="text-xs text-zinc-400">{{ $online->count() }} online</span>
        </div>

        {{-- Card grid that wraps responsively --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 px-4 pb-4 sm:px-5 pt-3">
            @forelse ($online as $u)
                <article class="rounded-xl border border-zinc-200 p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium text-zinc-900">{{ $u->name }}</p>
                            <p class="text-sm text-zinc-500 break-all leading-tight">{{ $u->email }}</p>
                            <p class="mt-1 text-xs text-zinc-400">
                                Last seen {{ optional($u->last_seen_at)->diffForHumans() ?? '—' }}
                            </p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full border border-green-200 bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                            online
                        </span>
                    </div>
                </article>
            @empty
                <p class="col-span-full px-2 py-3 text-sm text-zinc-500">No coordinators online.</p>
            @endforelse
        </div>
    </section>

    {{-- Pending / Approved split --}}
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Pending --}}
        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="flex items-center justify-between px-4 py-3 sm:px-5 border-b">
                <h2 class="text-sm font-medium text-zinc-700">Pending</h2>
                <span class="text-xs text-zinc-400">{{ $pending->count() }} total</span>
            </div>

            @if($pending->isEmpty())
                <p class="px-4 py-6 sm:px-5 text-sm text-zinc-500">No pending.</p>
            @else
                {{-- 3 columns on lg+ --}}
                <div class="p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($pending as $u)
                        <article class="h-full rounded-xl border border-zinc-200 bg-white p-4 shadow-sm flex flex-col justify-between">
                            <div class="min-w-0 space-y-1">
                                <p class="font-medium text-zinc-900 truncate">{{ $u->name }}</p>
                                <p class="text-sm text-zinc-500 break-all leading-tight">{{ $u->email }}</p>
                            </div>

                            {{-- CONSISTENT BUTTON GROUP: 2 equal columns, full-width buttons --}}
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <button
                                    wire:click="approve({{ $u->id }})"
                                    class="w-full inline-flex items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                                >
                                    Approve
                                </button>
                                <button
                                    wire:click="delete({{ $u->id }})"
                                    class="w-full inline-flex items-center justify-center rounded-lg border border-red-500 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100 focus:outline-none focus:ring-4 focus:ring-red-100"
                                >
                                    Delete
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Approved --}}
        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="flex items-center justify-between px-4 py-3 sm:px-5 border-b">
                <h2 class="text-sm font-medium text-zinc-700">Approved</h2>
                <span class="text-xs text-zinc-400">{{ $approved->count() }} total</span>
            </div>

            @if($approved->isEmpty())
                <p class="px-4 py-6 sm:px-5 text-sm text-zinc-500">No approved coordinators.</p>
            @else
                {{-- 3 columns on lg+ --}}
                <div class="p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($approved as $u)
                        <article class="h-full rounded-xl border border-zinc-200 bg-white p-4 shadow-sm flex flex-col justify-between">
                            <div class="min-w-0 space-y-1">
                                <p class="font-medium text-zinc-900 truncate">{{ $u->name }}</p>
                                <p class="text-sm text-zinc-500 break-all leading-tight">{{ $u->email }}</p>
                                <p class="mt-1 text-xs text-zinc-400">
                                    Last seen {{ optional($u->last_seen_at)->diffForHumans() ?? '—' }}
                                </p>
                            </div>

                            {{-- CONSISTENT BUTTON GROUP --}}
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <button
                                    wire:click="unapprove({{ $u->id }})"
                                    class="w-full inline-flex items-center justify-center rounded-lg bg-red-500 px-3 py-2 text-sm font-medium text-white shadow hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                                >
                                    Move to Pending
                                </button>
                                <button
                                    wire:click="delete({{ $u->id }})"
                                    class="w-full inline-flex items-center justify-center rounded-lg border border-red-500 bg-red-50 px-3 py-2 text-sm font-medium text-red-900 hover:bg-red-100 focus:outline-none focus:ring-4 focus:ring-red-100"
                                >
                                    Delete
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
