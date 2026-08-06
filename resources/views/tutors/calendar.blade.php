@extends('layouts.tutors-layout')

@section('sidebar')
    @include('components.tutors-sidebar')
@endsection

@section('filter-bar')
    <div style="flex: 1; display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0;">
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-main);">My Tuition Calendar</h2>
        <a href="{{ route('tutors.schedule.availability') }}" style="background: var(--secondary-blue); color: white; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.85rem;">
            <i class="fa-solid fa-clock"></i> Edit Availability Hours
        </a>
    </div>
@endsection

@section('content')
    <div style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
        <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.5rem; box-shadow: var(--shadow-sm);">
            <div id="fullCalendar"></div>
        </div>
    </div>

    <!-- FullCalendar JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('fullCalendar');
            const events = @json($events);

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: events,
                eventClick: function(info) {
                    const eventObj = info.event;
                    const props = eventObj.extendedProps;

                    let msg = `Student: ${props.student_name}\nSubject: ${props.subject_name}\nStatus: ${props.status}`;
                    if (props.meeting_link) {
                        msg += `\nMeeting Link: ${props.meeting_link}`;
                    }
                    alert(msg);
                }
            });
            calendar.render();
        });
    </script>
@endsection
