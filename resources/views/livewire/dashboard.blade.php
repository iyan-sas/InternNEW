@php
    $bgColors = [
        ['header' => '#BFDBFE', 'border' => 'border-blue-400'],
        ['header' => '#86EFAC', 'border' => 'border-green-400'],
        ['header' => '#FDE68A', 'border' => 'border-yellow-400'],
        ['header' => '#F9A8D4', 'border' => 'border-pink-400'],
        ['header' => '#D8B4FE', 'border' => 'border-purple-400'],
        ['header' => '#FCA5A5', 'border' => 'border-red-400'],
    ];
@endphp

<div class="min-h-screen bg-white dark:bg-zinc-900 p-6 rounded-lg shadow"
     x-data="{
        showConfirm:false,
        actionType:'',
        streamId:null,
        confirm(action,id){
            this.actionType=action;
            this.streamId=id;
            this.showConfirm=true;
        },
        proceed(){
            if(this.actionType==='edit'){
                @this.edit(this.streamId);
            } else if(this.actionType==='delete'){
                @this.delete(this.streamId);
            }
            this.showConfirm=false;
        }
     }"
     @open-confirm.window="confirm($event.detail.action,$event.detail.id)">

    @livewire('create-class-form')
    @livewire('edit-class-form')

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-4">
        @foreach ($streams as $index => $stream)
            @php
                $colors = $bgColors[$index % count($bgColors)];
                $campus = $stream->campus ?? $stream->section;
                $college = $stream->college ?? $stream->subject;
            @endphp

            <div
                wire:key="stream-{{ $stream->id }}"
                class="rounded-md shadow-sm border {{ $colors['border'] }}"
                x-data="{ open:false }"
            >
                <!-- Header -->
                <div class="px-4 py-2 rounded-t-md flex justify-between items-center"
                     style="background-color: {{ $colors['header'] }};">
                    <div class="w-full">
                        <a href="{{ route('class.show', $stream->invite_token) }}"
                           class="block font-bold text-lg text-black hover:underline focus:outline-none">
                            {{ $stream->class_name }}
                        </a>
                    </div>

                    <!-- Dropdown -->
                    <div class="relative" @click.outside="open = false">
                        <button @click="open = !open" class="text-black text-2xl hover:text-gray-700">
                            &#x22EE;
                        </button>
                        <div
                            x-show="open"
                            x-transition
                            x-cloak
                            class="absolute right-0 mt-2 w-32 bg-white border border-gray-200 rounded shadow-lg z-10 text-black"
                        >
                            <button
                                @click="$dispatch('open-confirm', {action:'edit', id: {{ $stream->id }}})"
                                class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100"
                            >
                                Edit
                            </button>
                            <button
                                @click="$dispatch('open-confirm', {action:'delete', id: {{ $stream->id }}})"
                                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-4 bg-white text-sm text-zinc-800 space-y-1">
                    <p><span class="font-semibold">Campus:</span> {{ $campus ?: '—' }}</p>
                    <p><span class="font-semibold">College:</span> {{ $college ?: '—' }}</p>

                    @if(auth()->user()->role === 'admin')
                        <div class="mt-2 space-y-1 break-words text-sm">
                            @if(!empty($stream->coordinator_token))
                                <p>
                                    <span class="font-semibold">Coordinator Invite:</span><br>
                                    <a href="{{ url('/coordinator/join/' . $stream->coordinator_token) }}"
                                       class="text-blue-600 underline break-all">
                                        {{ url('/coordinator/join/' . $stream->coordinator_token) }}
                                    </a>
                                </p>
                            @endif
                            @if(!empty($stream->student_token))
                                <p>
                                    <span class="font-semibold">Student Invite:</span><br>
                                    <a href="{{ url('/student/join/' . $stream->student_token) }}"
                                       class="text-green-600 underline break-all">
                                        {{ url('/student/join/' . $stream->student_token) }}
                                    </a>
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- SINGLE CONFIRMATION MODAL (small, centered box) -->
    <div x-show="showConfirm"
         x-transition.opacity.duration.150ms
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div
            x-show="showConfirm"
            x-transition.scale.origin.center.duration.150ms
            class="bg-white rounded-2xl shadow-2xl text-center p-6 w-[420px] max-w-[92vw] mx-4"
        >
            <!-- ✅ Use x-text so the title actually renders -->
            <h2 class="text-lg font-semibold text-zinc-900 mb-2"
                x-text="actionType==='edit'
                    ? 'Are you sure you want to edit this?'
                    : 'Are you sure you want to delete this?'">
            </h2>

            <!-- Helper text only for delete -->
            <p class="text-sm text-zinc-600 mb-4" x-show="actionType==='delete'">
                This action cannot be undone.
            </p>

            <div class="flex justify-end gap-3">
                <button @click="showConfirm=false"
                        class="px-4 py-2 rounded-lg bg-red-500 hover:bg-zinc-200 text-white">
                    Cancel
                </button>
                <button @click="proceed()"
                        class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white">
                    Yes
                </button>
            </div>
        </div>
    </div>
</div>
