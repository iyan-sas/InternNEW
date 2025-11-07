<div>
    @if (($showModal ?? false) === true)
        {{-- Backdrop (click to close) --}}
        <div class="fixed inset-0 bg-black/40 backdrop-blur-[1px] z-40" wire:click="cancel"></div>

        {{-- Modal --}}
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            wire:keydown.escape.window="cancel"
            role="dialog"
            aria-modal="true"
            aria-labelledby="edit-class-title"
        >
            <div
                class="w-full bg-white border border-slate-200 ring-1 ring-blue-500/10 rounded-3xl overflow-hidden shadow-2xl"
                style="width:700px; max-width:92vw; border-radius:28px;
                       box-shadow:0 20px 64px rgba(37,99,235,.26), 0 10px 28px rgba(37,99,235,.12);"
            >
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-slate-200 bg-white/80">
                    <h2 id="edit-class-title" class="text-base font-semibold">Edit Class</h2>
                </div>

                {{-- Body --}}
                <div class="p-6">
                    @if (session('success'))
                        <div class="mb-4 rounded-full border border-green-200 bg-green-50 px-3 py-2 text-xs text-green-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form wire:submit.prevent="save" class="space-y-4">
                        {{-- Campus --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">Campus</label>
                            <select
                                wire:model.live="campus"
                                class="w-full h-11 rounded-xl border border-gray-300 px-3 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Select Campus</option>
                                @foreach ($campusOptions as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('campus') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- College --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">College</label>
                            <select
                                wire:model.live="college"
                                @disabled(empty($collegeOptions))
                                class="w-full h-11 rounded-xl border border-gray-300 px-3 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                       {{ empty($collegeOptions) ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : '' }}"
                            >
                                <option value="">
                                    {{ empty($collegeOptions) ? 'Select Campus first' : 'Select College' }}
                                </option>
                                @foreach ($collegeOptions as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('college') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Actions --}}
                        <div class="flex justify-end gap-2 pt-4">
                            <button
                                type="button"
                                wire:click="cancel"
                                class="px-5 py-2 rounded-full bg-red-500 text-white hover:bg-red-600"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-6 py-2 rounded-full bg-blue-600 text-white hover:bg-blue-700"
                            >
                                Save
                            </button>
                        </div>
                    </form>
                </div>
                {{-- /Body --}}
            </div>
        </div>
    @endif
</div>
