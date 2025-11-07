<x-layouts.app :title="__('Calendar')">
    <div 
        wire:ignore 
        x-data 
        x-init="
            $nextTick(() => {
                const calendarEl = document.getElementById('calendar');
                if (!calendarEl) return;

                if (!window.calendarInstance) {
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        events: '/api/events',
                        editable: false,
                        selectable: true,
                        eventDisplay: 'block',
                    });

                    calendar.render();
                    window.calendarInstance = calendar;

                    window.refreshCalendarEvents = () => calendar.refetchEvents();
                }
            })
        "
        class="p-4 min-h-screen bg-white dark:bg-zinc-900 rounded shadow"
    >
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Class Calendar</h1>
        <div id="calendar" class="rounded-lg shadow-md"></div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    @endpush
</x-layouts.app>
