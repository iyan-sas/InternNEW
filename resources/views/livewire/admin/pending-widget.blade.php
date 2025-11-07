@if (!auth()->user()?->is_creator)
    {{-- Only system creators see this --}}
    @php return; @endphp
@endif

<div class="bg-white border rounded-lg p-4">
    <h2 class="text-lg font-semibold mb-3">Pending Admin Approvals</h2>

    @if (session('success'))
        <p class="text-green-600 mb-3">{{ session('success') }}</p>
    @endif

    @if ($pending->isEmpty())
        <p class="text-gray-500">No pending admin accounts.</p>
    @else
        <ul class="divide-y">
            @foreach ($pending as $u)
                <li class="py-3 flex items-center justify-between">
                    <div>
                        <p class="font-medium">{{ $u->name }}</p>
                        <p class="text-sm text-gray-600">{{ $u->email }}</p>
                        <p class="text-xs text-gray-500">Requested: {{ $u->created_at->format('M d, Y h:i A') }}</p>
                    </div>

                    {{-- POST form to approve (uses your existing route) --}}
                    <form method="POST" action="{{ route('admin.users.approve', $u->id) }}">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1.5 bg-green-500 text-white rounded-lg hover:bg-green-500"
                                onclick="this.disabled=true; this.innerText='Approving…'; this.form.submit();">
                            Approve
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif
</div>
