<div class="border rounded-lg p-3">
    <div class="max-w-3xl mx-auto w-full">
        {{-- Section title --}}
        <h3 class="text-base sm:text-lg font-semibold text-slate-800 mb-2">
            Announcement
        </h3>

        <form wire:submit.prevent="post" class="space-y-3">
            <textarea
                wire:model.defer="message"
                rows="2"
                class="w-full border rounded-md p-2 resize-y min-h-20
                       focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-500"
                placeholder="Write announcement..."
            ></textarea>

            @error('message')
                <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror

            {{-- Actions: full-width on mobile, compact & right-aligned on md+ --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2">
                <button
                    type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center
                           rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white
                           hover:bg-blue-700 active:bg-blue-800"
                >
                    Post
                </button>
            </div>
        </form>
    </div>
</div>
