{{-- resources/views/livewire/admin/pending-users.blade.php --}}
<div class="p-6">
    {{-- Page Header --}}
    <h2 class="text-2xl font-semibold mb-6 text-left">Pending Admin Approvals</h2>

    {{-- Flash messages --}}
    @if (session('success'))
        <p class="mb-3 rounded-md bg-green-100 text-green-500 px-3 py-2 text-left">
            {{ session('success') }}
        </p>
    @endif

    @if (session('error'))
        <p class="mb-3 rounded-md bg-red-100 text-red-500 px-3 py-2 text-left">
            {{ session('error') }}
        </p>
    @endif

    @php
        $items = collect($pending ?? []);
    @endphp

    {{-- Empty state --}}
    @if ($items->isEmpty())
        <p class="text-gray-500 text-left">No pending admin accounts.</p>
    @else
        {{-- List --}}
        <ul class="w-full space-y-3">
            @foreach ($items as $u)
                @php
                    $id        = is_array($u) ? ($u['id'] ?? null)        : ($u->id ?? null);
                    $name      = is_array($u) ? ($u['name'] ?? '')        : ($u->name ?? '');
                    $email     = is_array($u) ? ($u['email'] ?? '')       : ($u->email ?? '');
                    $createdAt = is_array($u) ? ($u['created_at'] ?? null): ($u->created_at ?? null);
                    if (is_string($createdAt)) {
                        try { $createdAt = \Carbon\Carbon::parse($createdAt); } catch (\Throwable $e) { $createdAt = null; }
                    }
                @endphp

                <li class="w-full rounded-xl border bg-white px-5 py-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0 text-left">
                            <p class="font-medium text-gray-900">
                                {{ $name }}
                                <span class="ml-2 align-middle rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                                    Pending
                                </span>
                            </p>
                            <p class="text-sm text-gray-600">{{ $email }}</p>
                            <p class="text-xs text-gray-500">
                                Requested: {{ $createdAt ? $createdAt->format('M d, Y h:i A') : '—' }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('admin.users.approve', $id) }}" class="inline">
                                @csrf
                                <button
                                    type="submit"
                                    class="inline-flex items-center rounded-md bg-green-500 px-4 py-2 text-white text-sm font-medium hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-400 disabled:opacity-50"
                                    @disabled(!$id)
                                >
                                    Approve
                                </button>
                            </form>

                            @if (Route::has('admin.users.reject'))
                                <form method="POST" action="{{ route('admin.users.reject', $id) }}" class="inline">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="inline-flex items-center rounded-md bg-red-500 px-4 py-2 text-white text-sm font-medium hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-gray-300 disabled:opacity-50"
                                        @disabled(!$id)
                                    >
                                        Reject
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
