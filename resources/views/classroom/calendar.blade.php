<x-layouts.app>
    <div class="p-4">
        <h1 class="text-2xl font-bold mb-4">Calendar - {{ $stream->class_name }}</h1>

        <div id="calendar" class="bg-white p-4 rounded shadow min-h-[500px] z-0"></div>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
        <script>
            let calendarInstance;

            function initCalendar() {
                const el = document.getElementById('calendar');
                if (!el) return;

                if (calendarInstance) calendarInstance.destroy();

                calendarInstance = new FullCalendar.Calendar(el, {
                    initialView: 'dayGridMonth',
                    height: 'auto',
                    events: '/api/events?stream_id={{ $stream->id }}',
                });

                calendarInstance.render();

                window.calendar = calendarInstance;
                window.refreshCalendarEvents = () => calendarInstance.refetchEvents();
            }

            document.addEventListener('DOMContentLoaded', initCalendar);
            document.addEventListener('livewire:navigated', initCalendar);
            Livewire.hook('message.processed', initCalendar);
        </script>
    @endpush
</x-layouts.app>
