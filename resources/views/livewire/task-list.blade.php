{{-- resources/views/livewire/task-list.blade.php --}}
@php use Carbon\Carbon; @endphp

<div class="space-y-4">
    @foreach($tasks as $task)
        @php
            $user      = auth()->user();
            $roleStr   = $user ? strtolower((string) (optional($user->role)->value ?? $user->role)) : null;
            $canManage = in_array($roleStr, ['coordinator','admin']);
            $pastDue   = $task->due_date ? Carbon::now()->gt(Carbon::parse($task->due_date)->endOfDay()) : false;
        @endphp

        <div
            class="relative border rounded-lg bg-white shadow-sm overflow-visible"
            wire:key="task-{{ $task->id }}"
            x-data="{ open:false, menu:false }"
            x-cloak
        >
            {{-- HEADER --}}
            <div class="px-4 py-3 border-b flex justify-between items-start">
                <button type="button" class="text-left flex-1" @click="open = !open">
                    <div class="flex items-center gap-2">
                        <span class="inline-block text-gray-400 transition" x-text="open ? '▾' : '▸'"></span>
                        <h3 class="text-[15px] font-semibold text-gray-900">{{ $task->title }}</h3>
                    </div>

                    @if($task->instruction)
                        <p class="text-sm text-gray-700 mt-1" x-show="!open">
                            {{ \Illuminate\Support\Str::limit($task->instruction, 160) }}
                        </p>
                    @endif

                    <p class="text-xs text-gray-500 mt-1">
                        {{ $task->created_at->diffForHumans() }}
                        @if($task->due_date)
                            · <span class="{{ $pastDue ? 'text-red-600' : 'text-gray-600' }}">
                                Due: {{ Carbon::parse($task->due_date)->format('M d, Y') }}
                                @if($pastDue) — submissions closed @endif
                              </span>
                        @endif
                    </p>
                </button>

                {{-- 3-dot menu (coordinator/admin only) --}}
                @if($canManage && ($editId ?? null) !== $task->id)
                    <div class="relative shrink-0">
                        <button type="button"
                                class="p-1 rounded hover:bg-gray-100"
                                @click.stop="menu = !menu"
                                @keydown.escape.window="menu=false"
                                aria-haspopup="menu"
                                :aria-expanded="menu.toString()">
                            ⋮
                            <span class="sr-only">More options</span>
                        </button>

                        <div x-show="menu"
                             x-transition
                             @click.outside="menu=false"
                             class="absolute right-0 mt-2 w-44 bg-white border border-gray-200 rounded-md shadow-lg z-50">
                            <button type="button"
                                    class="block w-full text-left px-3 py-2 text-sm hover:bg-gray-50"
                                    wire:click="startEdit({{ $task->id }})"
                                    @click.stop="menu=false; open=true">
                                Edit
                            </button>
                            <button type="button"
                                    class="block w-full text-left px-3 py-2 text-sm text-red-500 hover:bg-red-50"
                                    wire:click="deleteTask({{ $task->id }})"
                                    wire:confirm="Delete this task?"
                                    @click.stop>
                                Delete
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- INLINE EDIT (coordinator/admin) --}}
            @if($canManage && ($editId ?? null) === $task->id)
                <div class="px-4 pt-3 pb-4 border-b bg-yellow-50">
                    <div class="mb-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" class="w-full border rounded px-3 py-2"
                               wire:model.defer="editTitle">
                        @error('editTitle') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Instruction</label>
                        <textarea rows="4" class="w-full border rounded px-3 py-2"
                                  wire:model.defer="editInstruction"></textarea>
                        @error('editInstruction') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                        <input type="date" class="w-full border rounded px-3 py-2"
                               wire:model.defer="editDueDate">
                        @error('editDueDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" class="px-3 py-1.5 rounded bg-blue-500 text-white"
                                wire:click="updateTask">Save</button>
                        <button type="button" class="px-3 py-1.5 rounded bg-gray-400 text-white"
                                wire:click="cancelEdit">Cancel</button>
                    </div>
                </div>
            @endif

            {{-- BODY --}}
            <div class="px-4 py-4" x-show="open" x-transition>
                @if($task->instruction)
                    <p class="text-sm text-gray-800 mb-3 whitespace-pre-line">{{ $task->instruction }}</p>
                @endif

                {{-- STUDENT uploads --}}
                @if($roleStr === 'student')
                    <div class="mb-4">
                        @if($pastDue)
                            <p class="text-sm text-red-500">Submissions are closed for this task.</p>
                        @else
                            <div class="flex items-center gap-2">
                                <input
                                    type="file"
                                    multiple
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip"
                                    wire:model="uploads.{{ $task->id }}"
                                    wire:key="fileinput-{{ $task->id }}"
                                    class="block w-full text-sm border rounded px-2 py-1"
                                >
                                <button type="button"
                                        class="px-3 py-2 rounded bg-green-500 text-white"
                                        wire:click="submitMultiple({{ $task->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="uploads.{{ $task->id }},submitMultiple">
                                    Upload
                                </button>
                            </div>

                            {{-- top-level error set by component --}}
                            @error("uploads.{$task->id}")
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror

                            {{-- per-file errors --}}
                            @foreach($errors->get("uploads.{$task->id}.*") as $errArr)
                                @foreach($errArr as $err)
                                    <p class="text-red-500 text-sm">{{ $err }}</p>
                                @endforeach
                            @endforeach

                            {{-- uploading indicator --}}
                            <div class="text-sm text-blue-600 mt-1"
                                 wire:loading
                                 wire:target="uploads.{{ $task->id }}">
                                Uploading… please wait.
                            </div>
                        @endif

                        @php($my = $task->submissions->where('student_id', optional(auth()->user())->id))
                        @if($my->count())
                            <div class="mt-3">
                                <p class="text-sm font-medium">Your submissions:</p>
                                <ul class="list-disc ml-5 text-sm space-y-2">
                                    @foreach($my as $s)
                                        <li class="flex flex-col gap-1" wire:key="mine-{{ $s->id }}">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <a href="{{ route('submissions.show', $s->id) }}"
                                                       target="_blank"
                                                       class="text-blue-600 underline">
                                                        {{ $s->original_name ?? basename($s->file_path) }}
                                                    </a>
                                                    <span class="text-gray-500">· {{ $s->created_at->diffForHumans() }}</span>
                                                </div>

                                                <div class="flex gap-2">
                                                    @if(($editingId ?? null) === $s->id)
                                                        <div class="flex items-center gap-2">
                                                            <input type="file"
                                                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip"
                                                                   wire:model="newFile"
                                                                   class="text-sm border rounded px-2 py-1">
                                                            <button type="button"
                                                                    class="px-2 py-1 text-xs rounded bg-green-500 text-white"
                                                                    wire:click="updateSubmission"
                                                                    wire:loading.attr="disabled">
                                                                Save
                                                            </button>
                                                            <button type="button"
                                                                    class="px-2 py-1 text-xs rounded bg-gray-400 text-white"
                                                                    wire:click="$set('editingId', null)">
                                                                Cancel
                                                            </button>
                                                        </div>
                                                        @error('newFile')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                                    @else
                                                        <button type="button"
                                                                class="px-2 py-1 text-xs rounded bg-yellow-500 text-white"
                                                                wire:click="editSubmission({{ $s->id }})">
                                                            Edit
                                                        </button>
                                                        <button type="button"
                                                                class="px-2 py-1 text-xs rounded bg-red-500 text-white"
                                                                wire:click="deleteSubmission({{ $s->id }})"
                                                                wire:confirm="Delete this file?"
                                                                @click.stop>
                                                            Delete
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Status badge + remark --}}
                                            @if($s->review_status)
                                                <div class="flex items-start gap-2">
                                                    @if($s->review_status === 'approved')
                                                        <span class="inline-flex items-center px-2 py-0.5 text-xs rounded bg-white text-green-500">Approved</span>
                                                    @elseif($s->review_status === 'needs_revision')
                                                        <span class="inline-flex items-center px-2 py-0.5 text-xs rounded bg-white text-yellow-500">Needs revision</span>
                                                    @endif
                                                    @if($s->review_remark)
                                                        <p class="text-xs text-gray-600">“{{ $s->review_remark }}”
                                                            @if($s->reviewer) — <span class="italic">{{ $s->reviewer->name }}</span>@endif
                                                        </p>
                                                    @endif
                                                </div>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- COORD/ADMIN: submissions + review controls --}}
                @if($canManage)
                    <h4 class="font-semibold">Submissions ({{ $task->submissions->count() }})</h4>

                    @if($task->submissions->isEmpty())
                        <p class="text-sm text-gray-500 mt-2">No submissions.</p>
                    @else
                        <div class="mt-3 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                <tr class="text-left border-b">
                                    <th class="py-2 pr-3">Student</th>
                                    <th class="py-2 pr-3">File</th>
                                    <th class="py-2 pr-3">Submitted</th>
                                    <th class="py-2 pr-3">Status</th>
                                    <th class="py-2 pr-3">Review</th>
                                    <th class="py-2">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($task->submissions as $submission)
                                    <tr class="border-b align-top" wire:key="row-{{ $submission->id }}">
                                        <td class="py-2 pr-3">
                                            {{ $submission->student?->name ?? 'Unknown' }}
                                            <span class="text-gray-500">({{ $submission->student?->email }})</span>
                                        </td>
                                        <td class="py-2 pr-3">
                                            <a href="{{ route('submissions.show', $submission->id) }}"
                                               target="_blank" class="text-blue-600 underline">
                                                {{ $submission->original_name ?? basename($submission->file_path) }}
                                            </a>
                                        </td>
                                        <td class="py-2 pr-3">{{ $submission->created_at->format('Y-m-d H:i') }}</td>
                                        <td class="py-2 pr-3">
                                            @if($submission->review_status === 'approved')
                                                <span class="inline-flex items-center px-2 py-0.5 text-xs rounded bg-white text-green-600">Approved</span>
                                            @elseif($submission->review_status === 'needs_revision')
                                                <span class="inline-flex items-center px-2 py-0.5 text-xs rounded bg-white text-yellow-600">Needs revision</span>
                                            @else
                                                <span class="text-xs text-gray-400">—</span>
                                            @endif
                                            @if($submission->review_remark)
                                                <div class="text-xs text-gray-600 mt-1">
                                                    “{{ $submission->review_remark }}”
                                                    @if($submission->reviewer) — <span class="italic">{{ $submission->reviewer->name }}</span>@endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-2 pr-3">
                                            <div class="flex flex-col gap-1">
                                                <select class="border rounded px-2 py-1 text-sm"
                                                        wire:model.defer="statuses.{{ $submission->id }}">
                                                    <option value="" @selected(($statuses[$submission->id] ?? '')==='')>Set status…</option>
                                                    <option value="approved">Approved</option>
                                                    <option value="needs_revision">Needs revision</option>
                                                </select>
                                                <input type="text"
                                                       placeholder="Remark (req. if needs revision)"
                                                       class="border rounded px-2 py-1 text-sm"
                                                       wire:model.defer="remarks.{{ $submission->id }}">
                                                <button type="button"
                                                        class="self-start px-2 py-1 text-xs rounded bg-blue-600 text-white"
                                                        wire:click="saveReview({{ $submission->id }})">
                                                    Save
                                                </button>
                                            </div>
                                            @error("statuses.{$submission->id}")<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                                            @error("remarks.{$submission->id}")<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                                        </td>
                                        <td class="py-2">
                                            <button type="button"
                                                    class="px-2 py-1 text-xs rounded bg-red-600 text-white"
                                                    wire:click="deleteSubmission({{ $submission->id }})"
                                                    wire:confirm="Delete this submission?"
                                                    @click.stop>
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endforeach
</div>
