{{-- resources/views/your-coordinator-or-shared-calendar.blade.php --}}
<x-layouts.app :title="__('Calendar')">
    <div class="p-3 sm:p-4 md:p-6 w-full max-w-full">
        <!-- 📅 Page Title -->
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">
                {{ __('Calendar') }}
            </h1>
        </div>

        <!-- 🗓 Calendar Card -->
        <div id="calendar-card" class="border border-gray-200 rounded-2xl shadow-sm bg-white">
            <div class="p-3 sm:p-4 lg:p-6 overflow-x-auto">
                <div id="calendar" class="min-h-[420px] sm:min-h-[560px] lg:min-h-[640px] w-full max-w-full z-0"></div>
            </div>
        </div>

        {{-- Livewire components (same as admin) --}}
        @livewire('calendar-event-form')
        @livewire('event-list')
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
        <style>
            [x-cloak]{ display:none !important; } /* Alpine anti-flash */

            /* Toolbar wraps nicely on small screens */
            .fc .fc-toolbar { flex-wrap: wrap; gap: .5rem; }
            .fc .fc-toolbar-title{
                font-size: clamp(1rem, 2.6vw, 1.25rem) !important;
                font-weight: 700 !important;
                color: #1e3a8a !important;
            }

            /* Compact buttons */
            .fc .fc-button{ padding:.35rem .6rem; border-radius:.6rem; }

            /* Slightly denser grid on phones */
            @media (max-width: 480px){
                .fc td,.fc th{ font-size:.8rem; }
                .fc .fc-daygrid-day-frame{ padding:.25rem; }
                .fc .fc-daygrid-event{ margin-top:.15rem; }
            }

            /* Softer borders */
            .fc-theme-standard .fc-scrollgrid,
            .fc-theme-standard td, .fc-theme-standard th {
                border-color: rgba(0,0,0,.06);
            }

            /* Fluid width */
            #calendar .fc { width: 100% !important; max-width: 100% !important; }

            /* Highlight today */
            .fc-day-today { background-color:#dbeafe!important; font-weight:bold; border-radius:6px; }

            /* Event style */
            .fc-event { border-radius:6px!important; padding:2px 4px!important; font-size:.85rem!important; transition:transform .15s; }
            .fc-event:hover { transform:scale(1.05); }

            /* Day hover */
            .fc-daygrid-day:hover { background-color:#f9fafb!important; }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
        <script>
            let calendarInstance;

            function bindCalendarEventListeners() {
                if (window.__calendarListenersBound) return;
                window.__calendarListenersBound = true;

                window.addEventListener('calendar:refresh', () => {
                    if (calendarInstance) calendarInstance.refetchEvents();
                });

                window.addEventListener('calendar:event-saved', (e) => {
                    if (!calendarInstance) return;
                    const data = e.detail || {};
                    const id = String(data.id || '');
                    if (!id) return;

                    const existing = calendarInstance.getEventById(id);
                    if (existing) {
                        if (data.title) existing.setProp('title', data.title);
                        if (data.start) existing.setStart(data.start);
                        existing.setAllDay(false);
                    } else {
                        calendarInstance.addEvent({
                            id,
                            title: data.title ?? 'Untitled',
                            start: data.start,
                            allDay: false,
                        });
                    }
                });

                // (We’ll also add a direct listener after render; keeping this for safety)
                window.addEventListener('calendar:event-deleted', (e) => {
                    if (!calendarInstance) return;
                    const id = String((e.detail || {}).id || '');
                    const ev = id ? calendarInstance.getEventById(id) : null;
                    if (ev) ev.remove();
                });
            }

            // ----- Responsive helpers
            const isMobile = () => window.matchMedia('(max-width: 640px)').matches;
            const isTablet = () => window.matchMedia('(min-width: 641px) and (max-width: 1024px)').matches;

            function applyResponsive(calendar) {
                if (isMobile()) {
                    calendar.setOption('headerToolbar', {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay' // show all on phones
                    });
                    calendar.setOption('views', { dayGridMonth: { dayMaxEventRows: 2 } });
                    calendar.setOption('aspectRatio', 0.9);
                } else if (isTablet()) {
                    calendar.setOption('headerToolbar', {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek'
                    });
                    calendar.setOption('views', { dayGridMonth: { dayMaxEventRows: 3 } });
                    calendar.setOption('aspectRatio', 1.2);
                } else {
                    calendar.setOption('headerToolbar', {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    });
                    calendar.setOption('views', { dayGridMonth: { dayMaxEventRows: 4 } });
                    calendar.setOption('aspectRatio', 1.35);
                }
                calendar.updateSize();
            }

            function loadCalendar() {
                const el = document.getElementById('calendar');
                if (!el || typeof FullCalendar === 'undefined') return;

                if (calendarInstance) calendarInstance.destroy();

                calendarInstance = new FullCalendar.Calendar(el, {
                    initialView: 'dayGridMonth',
                    height: 'auto',
                    contentHeight: 'auto',
                    expandRows: true,
                    stickyHeaderDates: true,
                    timeZone: 'local',
                    eventColor: '#60a5fa',
                    eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short', hour12: true },
                    displayEventTime: true,
                    firstDay: 0, // Sunday

                    // Role-aware endpoint (returns posted_by & posted_role)
                    events: '{{ route('calendar.events') }}',

                    eventDidMount(info) {
                        const by   = info.event.extendedProps?.posted_by;
                        const role = (info.event.extendedProps?.posted_role || '').toLowerCase();
                        const desc = info.event.extendedProps?.description;

                        // AM/PM tweak
                        const timeEl = info.el.querySelector('.fc-event-time');
                        if (timeEl && timeEl.textContent) {
                            timeEl.textContent = timeEl.textContent
                                .replace(/(\d{1,2}:\d{2})\s*([ap])\b/i, (_, t, m) => `${t} ${m.toUpperCase()}M`)
                                .replace(/(\d{1,2})\s*([ap])\b/i,        (_, t, m) => `${t} ${m.toUpperCase()}M`);
                        }

                        // Append poster + role beside the title
                        if (by) {
                            const titleEl = info.el.querySelector('.fc-event-title');
                            if (titleEl) {
                                const who = document.createElement('span');
                                who.textContent = ` — ${by}${role ? ' ('+role+')' : ''}`;
                                who.style.fontSize = '0.75rem';
                                who.style.opacity  = '0.85';
                                titleEl.appendChild(who);
                            }
                        }

                        // Tooltip
                        const parts = [];
                        if (by)   parts.push(`By: ${by}${role ? ' ('+role+')' : ''}`);
                        if (desc) parts.push(desc);
                        if (parts.length) info.el.setAttribute('title', parts.join('\n'));

                        // Highlight admin events
                        if (role === 'admin') {
                            info.el.style.borderColor     = '#fda4af'; // rose-300
                            info.el.style.backgroundColor = '#fff1f2'; // rose-50
                            info.el.style.color           = '#9f1239'; // rose-800
                        }
                    },
                });

                calendarInstance.render();

                // 🔗 Add the listener right after render (as requested)
                window.addEventListener('calendar:event-deleted', (e) => {
                    const id = String(e?.detail?.id || '');
                    if (!id || !window.calendar) return;
                    const ev = window.calendar.getEventById(id);
                    if (ev) ev.remove();
                });

                bindCalendarEventListeners();
                applyResponsive(calendarInstance);

                // Re-apply responsive on window resize
                window.addEventListener('resize', () => applyResponsive(calendarInstance));

                // Reflow when container width changes (e.g., sidebar toggles)
                const shell = document.getElementById('calendar-card');
                if (window.ResizeObserver && shell) {
                    new ResizeObserver(() => calendarInstance.updateSize()).observe(shell);
                }

                // Expose helpers
                window.calendar = calendarInstance;
                window.refreshCalendarEvents = () => calendarInstance.refetchEvents();
            }

            document.addEventListener('DOMContentLoaded', loadCalendar);
            document.addEventListener('livewire:navigated', loadCalendar);
        </script>
    @endpush
</x-layouts.app>
