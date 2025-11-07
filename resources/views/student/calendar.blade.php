{{-- resources/views/student/calendar.blade.php --}}
<x-layouts.app :title="__('Calendar')">
    <div class="p-3 sm:p-4 md:p-6 w-full max-w-full">
        <!-- 📅 Page Title -->
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">{{ __('Calendar') }}</h1>
        </div>

        <!-- 🗓 Calendar Card -->
        <div id="calendar-card" class="border border-gray-200 rounded-2xl shadow-sm bg-white">
            <div class="p-3 sm:p-4 lg:p-6 overflow-x-auto">
                <div id="calendar" class="min-h-[420px] sm:min-h-[560px] lg:min-h-[640px] w-full max-w-full z-0"></div>
            </div>
        </div>

        <!-- 📌 Below the calendar: Upcoming + Notifications -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mt-4 sm:mt-6">
            <!-- Upcoming Events -->
            <div class="bg-white border rounded-2xl shadow-sm p-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-lg">📅 Upcoming Events</h3>
                </div>

                @forelse($upcoming as $e)
                    @php
                        try {
                            $formatted = $e->date
                                ? \Carbon\Carbon::parse($e->date)->timezone(config('app.timezone'))->format('M d, Y h:i A')
                                : null;
                        } catch (\Throwable $t) { $formatted = null; }

                        // role-safe parsing (enum or string)
                        $roleRaw = optional($e->user)->role;
                        $roleStr = null;
                        if (is_string($roleRaw)) {
                            $roleStr = $roleRaw;
                        } elseif (is_object($roleRaw)) {
                            if (method_exists($roleRaw, 'value')) $roleStr = $roleRaw->value;
                            elseif (property_exists($roleRaw, 'name')) $roleStr = $roleRaw->name;
                            else $roleStr = (string) $roleRaw;
                        }
                        $roleLower = $roleStr ? strtolower($roleStr) : null;

                        // 🔴 admin badge = red, else blue
                        $badgeClasses = $roleLower === 'admin'
                            ? 'border-rose-300 bg-rose-50 text-rose-700'
                            : 'border-blue-300 bg-blue-50 text-blue-700';
                    @endphp

                    <div class="py-3 border-b last:border-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <div class="font-medium text-gray-900">{{ $e->title }}</div>

                            {{-- Poster tag/badge --}}
                            @if(optional($e->user)->name)
                                <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs {{ $badgeClasses }}">
                                    {{ $e->user->name }}
                                    @if($roleLower)
                                        <span class="opacity-75">({{ $roleLower }})</span>
                                    @endif
                                </span>
                            @endif
                        </div>

                        @if($formatted)
                            <div class="text-sm text-gray-600 mt-0.5">{{ $formatted }}</div>
                        @endif

                        @if($e->description)
                            <div class="text-xs text-gray-500 mt-1">{{ $e->description }}</div>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 text-sm mt-2">No upcoming events.</p>
                @endforelse
            </div>

            <!-- Notifications -->
            <div class="bg-white border rounded-2xl shadow-sm p-4">
                <!-- Header -->
                <div class="flex items-center justify-between gap-2 flex-wrap mb-2">
                    <h3 class="font-semibold text-lg">🔔 Notifications</h3>

                    {{-- ✅ GET link with redirect back to this page --}}
                    <a
                        href="{{ route('notifications.read-all', ['redirect' => url()->current()]) }}"
                        class="shrink-0 inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                    >
                        Mark all as read
                    </a>
                </div>

                {{-- ✅ auto-dismiss flash (Alpine) --}}
                @if (session('status'))
                    <div
                        x-data="{ show: true }"
                        x-init="setTimeout(() => show = false, 2500)"
                        x-show="show"
                        x-transition.opacity.duration.400ms
                        x-cloak
                        class="mb-2 rounded-md border border-green-200 bg-green-50 px-3 py-2 text-xs text-green-800"
                        role="alert"
                    >
                        {{ session('status') }}
                    </div>
                @endif

                @forelse($notifs as $n)
                    @php
                        $rawLabel = $n->data['posted_label']
                            ?? ($n->data['posted_by']['name'] ?? ($n->data['posted_by'] ?? null));

                        $notifRole = $n->data['posted_role'] ?? ($n->data['posted_by']['role'] ?? null);
                        $notifRoleLower = $notifRole ? strtolower($notifRole) : null;

                        // Clean label: remove trailing " (role)" if already included to avoid duplication
                        $cleanLabel = $rawLabel;
                        if (is_string($rawLabel) && $notifRoleLower) {
                            $cleanLabel = preg_replace('/\s*\('.preg_quote($notifRoleLower, '/').'\)\s*$/i', '', $rawLabel);
                        }

                        // Role styles
                        $notifBadge = $notifRoleLower === 'admin'
                            ? 'border-rose-300 bg-rose-50 text-rose-700'
                            : 'border-amber-300 bg-amber-50 text-amber-800';
                        $accentBar = $notifRoleLower === 'admin' ? 'bg-rose-300' : 'bg-amber-300';

                        try {
                            $notifWhen = \Carbon\Carbon::parse($n->data['starts_at'] ?? now())
                                ->timezone(config('app.timezone'))->format('M d, Y h:i A');
                        } catch (\Throwable $t) { $notifWhen = null; }

                        // 🔗 event id for instant removal
                        $eid = $n->data['event_id'] ?? null;
                    @endphp

                    <a href="{{ $n->data['go_to'] ?? route('student.calendar') }}"
                       class="group block rounded-lg border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-colors mb-2 last:mb-0 relative overflow-hidden pl-3"
                       @if($eid) id="notif-{{ $eid }}" data-event-id="{{ $eid }}" @endif>
                        <!-- Accent bar -->
                        <span class="pointer-events-none absolute inset-y-0 left-0 w-1 rounded-l-lg {{ $accentBar }}"></span>

                        <div class="px-3 py-2">
                            <!-- Title -->
                            <div class="font-medium text-sm text-gray-900">
                                {{ $n->data['title'] ?? 'New Event' }}
                            </div>

                            <!-- Meta row -->
                            <div class="mt-1 flex items-center gap-2 flex-wrap text-xs text-gray-600">
                                @if($cleanLabel)
                                    <span class="inline-flex items-center max-w-[70%] sm:max-w-none whitespace-nowrap rounded-full border px-2 py-0.5 {{ $notifBadge }}">
                                        <span class="truncate">{{ $cleanLabel }}</span>
                                        @if($notifRoleLower)
                                            <span class="opacity-75 ml-1">({{ $notifRoleLower }})</span>
                                        @endif
                                    </span>
                                @endif

                                @if($notifWhen)
                                    <span class="hidden sm:inline">•</span>
                                    <span class="truncate">{{ $notifWhen }}</span>
                                @endif
                            </div>

                            @if(!empty($n->data['description']))
                                <div class="mt-1 text-xs text-gray-500"
                                     style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                    {{ $n->data['description'] }}
                                </div>
                            @endif
                        </div>
                    </a>
                @empty
                    <p class="text-gray-500 text-sm">No new notifications.</p>
                @endforelse
            </div>
        </div>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet" />
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
            .fc-day-today{ background-color:#dbeafe!important; font-weight:bold; border-radius:6px; }

            /* Event style */
            .fc-event{ border-radius:6px!important; padding:2px 4px!important; font-size:.85rem!important; transition:transform .15s; }
            .fc-event:hover{ transform:scale(1.05); }

            /* Day hover */
            .fc-daygrid-day:hover{ background-color:#f9fafb!important; }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
        <script>
            let calendarInstance;

            // ----- Responsive helpers
            const isMobile = () => window.matchMedia('(max-width: 640px)').matches;
            const isTablet = () => window.matchMedia('(min-width: 641px) and (max-width: 1024px)').matches;

            function applyResponsive(calendar) {
                if (isMobile()) {
                    calendar.setOption('headerToolbar', {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
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

                        // append poster name + (role)
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

                        // tooltip
                        const parts = [];
                        if (by) parts.push(`By: ${by}${role ? ' ('+role+')' : ''}`);
                        if (desc) parts.push(desc);
                        if (parts.length) info.el.setAttribute('title', parts.join('\n'));

                        // 🔴 highlight admin events
                        if (role === 'admin') {
                            info.el.style.borderColor     = '#fda4af';
                            info.el.style.backgroundColor = '#fff1f2';
                            info.el.style.color           = '#9f1239';
                        }
                    },
                });

                calendarInstance.render();
                applyResponsive(calendarInstance);

                // Re-apply responsive on window resize
                window.addEventListener('resize', () => applyResponsive(calendarInstance));

                // Reflow when container width changes (e.g., sidebar toggles)
                const shell = document.getElementById('calendar-card');
                if (window.ResizeObserver && shell) {
                    new ResizeObserver(() => calendarInstance.updateSize()).observe(shell);
                }

                // Expose helper
                window.calendar = calendarInstance;
                window.refreshCalendarEvents = () => calendarInstance.refetchEvents();
            }

            document.addEventListener('DOMContentLoaded', loadCalendar);

            // ✅ same-tab: remove from calendar and notifications on custom event
            window.addEventListener('calendar:event-deleted', (e) => {
                const id = String(e?.detail?.id || '');
                if (!id) return;

                if (window.calendar) {
                    const ev = window.calendar.getEventById(id);
                    if (ev) ev.remove();
                }

                const notif = document.querySelector(`[data-event-id="${CSS.escape(id)}"]`);
                if (notif) notif.remove();
            });
        </script>

        {{-- 🔊 Cross-user realtime (Plain Pusher JS; no Echo required) --}}
        <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
        <script>
            (function(){
                // Guard if pusher key is missing to avoid runtime errors
                const key = "{{ config('broadcasting.connections.pusher.key') }}";
                const cluster = "{{ config('broadcasting.connections.pusher.options.cluster') }}";

                if (!key || key === 'null') {
                    console.warn('[Calendar] Pusher key missing. Skipping realtime subscription.');
                    return;
                }

                const pusher = new Pusher(key, {
                    cluster: cluster || 'ap1',
                    forceTLS: true,
                });

                const channel = pusher.subscribe('events');
                channel.bind('event.deleted', function (e) {
                    const id = e?.id ? String(e.id) : '';
                    if (!id) return;

                    // remove from calendar
                    if (window.calendar) {
                        const ev = window.calendar.getEventById(id);
                        if (ev) ev.remove();
                    }

                    // remove notification card
                    const notif = document.querySelector(`[data-event-id="${CSS.escape(id)}"]`);
                    if (notif) notif.remove();
                });
            })();
        </script>

        {{-- If you prefer Laravel Echo, you can replace the block above with this:
        <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@^1/dist/echo.iife.js"></script>
        <script>
            (function(){
                const key = "{{ config('broadcasting.connections.pusher.key') }}";
                const cluster = "{{ config('broadcasting.connections.pusher.options.cluster') }}";
                if (!key || key === 'null') { console.warn('[Calendar] Pusher key missing.'); return; }

                const Echo = new window.Echo({
                    broadcaster: 'pusher',
                    key, cluster: cluster || 'ap1', forceTLS: true,
                });

                Echo.channel('events').listen('.event.deleted', (e) => {
                    const id = e?.id ? String(e.id) : '';
                    if (!id) return;
                    const ev = window.calendar?.getEventById(id);
                    if (ev) ev.remove();
                    const notif = document.querySelector(`[data-event-id="${CSS.escape(id)}"]`);
                    if (notif) notif.remove();
                });
            })();
        </script>
        --}}
    @endpush
</x-layouts.app>
