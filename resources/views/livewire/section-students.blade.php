<div class="space-y-2" wire:poll.10s>
    @php $students = $this->students; @endphp

    @if ($students->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Status</th>
                        @if (in_array($roleStr, ['admin','coordinator']))
                            <th class="px-4 py-2"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $i => $student)
                        @php
                            $status = strtolower((string) ($student->pivot->status ?? 'pending'));
                            $badgeCls =
                                $status === 'approved' ? 'bg-green-100 text-green-700' :
                                ($status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700');
                        @endphp
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $i + 1 }}</td>
                            <td class="px-4 py-2">{{ $student->name }}</td>
                            <td class="px-4 py-2">{{ $student->email }}</td>
                            <td class="px-4 py-2">
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $badgeCls }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>

                            @if (in_array($roleStr, ['admin','coordinator']))
                                <td class="px-4 py-2 text-right">
                                    <form id="remove-student-{{ $student->id }}" method="POST"
                                          action="{{ route('sections.students.destroy', ['section' => $sectionId, 'user' => $student->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-600 text-sm hover:text-red-800 hover:underline font-medium">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-sm text-blue-800">
                <strong>No students listed for this section yet.</strong>
            </p>
            <p class="text-xs text-blue-600 mt-2">
                Students appear here automatically after they join via the link (status starts as <em>Pending</em>).
            </p>
        </div>
    @endif
</div>
