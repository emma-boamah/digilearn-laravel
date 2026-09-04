@extends('layouts.tutors-layout')

@section('sidebar')
    @include('components.tutors-sidebar')
@endsection

@section('filter-bar')
    <div style="flex: 1; display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; width: 100%; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div>
                <h2 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-regular fa-calendar-days" style="color: var(--secondary-blue); font-size: 1.15rem;"></i>
                    My Tuition Calendar
                </h2>
                <p style="margin: 0.15rem 0 0 0; font-size: 0.8rem; color: var(--text-muted);">
                    Manage your live teaching schedule and upcoming sessions
                </p>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <a href="{{ route('tutors.bookings.index') }}" class="cal-action-btn cal-action-btn-secondary">
                <i class="fa-solid fa-list-check"></i>
                <span>Booking Requests</span>
            </a>
            <a href="{{ route('tutors.schedule.availability') }}" class="cal-action-btn cal-action-btn-primary">
                <i class="fa-solid fa-clock"></i>
                <span>Edit Availability Hours</span>
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="calendar-page-container">
        @if(count($availabilities) === 0)
            <!-- Unconfigured Availability Prompt -->
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 0.85rem 1.25rem; margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <strong style="font-size: 0.88rem; color: #92400e; display: block;">Weekly availability hours not configured yet</strong>
                        <span style="font-size: 0.78rem; color: #b45309;">Set your recurring working days and hours so students know your open slots.</span>
                    </div>
                </div>
                <a href="{{ route('tutors.schedule.availability') }}" class="cal-action-btn cal-action-btn-primary" style="font-size: 0.8rem; padding: 0.4rem 0.85rem;">
                    <i class="fa-solid fa-clock"></i>
                    <span>Set Availability</span>
                </a>
            </div>
        @endif

        <!-- Quick Stats / Helper Ribbon -->
        <div class="calendar-summary-ribbon">
            <div class="summary-stat-item">
                <div class="stat-icon-wrap" style="background: rgba(38, 119, 184, 0.1); color: var(--secondary-blue);">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <span class="stat-label">Booked Sessions</span>
                    <strong class="stat-value">{{ count($events) }} {{ Str::plural('Session', count($events)) }}</strong>
                </div>
            </div>
            
            <div class="summary-stat-item">
                <div class="stat-icon-wrap" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <span class="stat-label">Weekly Working Days</span>
                    <strong class="stat-value" style="color: #10b981;">
                        {{ count($availabilities) > 0 ? count($availabilities) . ' Days Active' : 'All Open' }}
                    </strong>
                </div>
            </div>

            <!-- Legend Items -->
            <div class="calendar-legend-wrap">
                <span class="legend-badge legend-available">
                    <span class="legend-dot" style="background: #ffffff; border: 2px solid #2677B8;"></span> Available Hours
                </span>
                <span class="legend-badge legend-booked">
                    <span class="legend-dot" style="background: #2677B8;"></span> Booked Lesson
                </span>
                <span class="legend-badge legend-completed">
                    <span class="legend-dot" style="background: #10b981;"></span> Completed
                </span>
                @if(count($blockedDates) > 0)
                    <span class="legend-badge legend-blocked">
                        <span class="legend-dot" style="background: #ef4444;"></span> Blocked Date
                    </span>
                @endif
                <span class="legend-badge legend-offhours">
                    <span class="legend-dot" style="background: #cbd5e1;"></span> Off-Hours
                </span>
            </div>
        </div>

        <!-- Main Calendar Card -->
        <div class="calendar-card">
            <div id="fullCalendar"></div>
        </div>
    </div>

    <!-- Modern Event Details Modal -->
    <div id="eventDetailModal" class="cal-modal-backdrop" style="display: none;" onclick="closeEventModal(event)">
        <div class="cal-modal-card" onclick="event.stopPropagation()">
            <div class="cal-modal-header">
                <div class="cal-modal-header-left">
                    <div id="modalStatusBadge" class="cal-status-pill">Scheduled</div>
                    <h3 id="modalStudentName" class="cal-modal-title">Student Name</h3>
                </div>
                <button type="button" class="cal-modal-close-btn" onclick="hideEventModal()" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="cal-modal-body">
                <div class="cal-detail-row">
                    <div class="cal-detail-icon"><i class="fa-solid fa-book-open"></i></div>
                    <div class="cal-detail-content">
                        <span class="cal-detail-label">Subject & Course</span>
                        <span id="modalSubject" class="cal-detail-value">Mathematics</span>
                    </div>
                </div>

                <div class="cal-detail-row">
                    <div class="cal-detail-icon"><i class="fa-regular fa-clock"></i></div>
                    <div class="cal-detail-content">
                        <span class="cal-detail-label">Scheduled Time</span>
                        <span id="modalTime" class="cal-detail-value">4:00 PM - 5:00 PM</span>
                    </div>
                </div>

                <div id="modalMeetingRow" class="cal-detail-row" style="display: none;">
                    <div class="cal-detail-icon" style="color: var(--secondary-blue); background: rgba(38, 119, 184, 0.1);"><i class="fa-solid fa-video"></i></div>
                    <div class="cal-detail-content">
                        <span class="cal-detail-label">Live Classroom Link</span>
                        <a id="modalMeetingLink" href="#" target="_blank" class="cal-meeting-btn">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            <span>Join Online Meeting</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="cal-modal-footer">
                <a href="{{ route('tutors.bookings.index') }}" class="cal-footer-btn-secondary">
                    <i class="fa-solid fa-list-check"></i> Manage in Requests
                </a>
                <button type="button" class="cal-footer-btn-primary" onclick="hideEventModal()">Done</button>
            </div>
        </div>
    </div>

    <!-- Floating Interactive Calendar Hover Tooltip -->
    <div id="calendarHoverTooltip" class="cal-hover-tooltip" style="display: none;"></div>

    <!-- Scoped Calendar Styles for Modern Light Mode -->
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        .calendar-page-container {
            padding: 1.5rem 2rem 2.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Top Action Buttons */
        .cal-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1.1rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .cal-action-btn-secondary {
            background: var(--bg-surface);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .cal-action-btn-secondary:hover {
            background: var(--gray-50);
            border-color: var(--gray-300);
            transform: translateY(-1px);
        }

        .cal-action-btn-primary {
            background: var(--secondary-blue);
            color: #ffffff;
            border: 1px solid transparent;
            box-shadow: 0 2px 4px rgba(38, 119, 184, 0.2);
        }

        .cal-action-btn-primary:hover {
            background: var(--secondary-blue-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(38, 119, 184, 0.25);
        }

        /* Summary Stats Ribbon */
        .calendar-summary-ribbon {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 1.5rem;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 0.85rem 1.25rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            flex-wrap: wrap;
        }

        .summary-stat-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .stat-icon-wrap {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
        }

        .stat-label {
            display: block;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-muted);
            font-weight: 600;
        }

        .stat-value {
            display: block;
            font-size: 0.95rem;
            color: var(--text-main);
            font-weight: 700;
        }

        /* Calendar Legend in Ribbon */
        .calendar-legend-wrap {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex-wrap: wrap;
        }

        .legend-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            background: var(--bg-main);
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            border: 1px solid var(--border-color);
        }

        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }

        /* Off-hours subtle diagonal stripe pattern */
        .fc-theme-standard .fc-timegrid-col.fc-day-disabled,
        .fc-theme-standard .fc-non-business {
            background-color: #f8fafc !important;
            background-image: repeating-linear-gradient(
                -45deg,
                rgba(226, 232, 240, 0.45),
                rgba(226, 232, 240, 0.45) 8px,
                rgba(248, 250, 252, 0.9) 8px,
                rgba(248, 250, 252, 0.9) 16px
            ) !important;
        }

        /* Blocked dates background in calendar */
        .fc-bg-event.cal-blocked-date-event {
            background: rgba(239, 68, 68, 0.12) !important;
            border: 1px dashed rgba(239, 68, 68, 0.35) !important;
        }

        /* Main Calendar Card */
        .calendar-card {
            background: var(--bg-surface);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            padding: 1.5rem;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04), 0 2px 6px -1px rgba(0, 0, 0, 0.02);
        }

        /* FullCalendar Custom Theme & Overrides */
        :root {
            --fc-border-color: #f1f5f9;
            --fc-page-bg-color: transparent;
            --fc-neutral-bg-color: #f8fafc;
            --fc-list-event-hover-bg-color: #f1f5f9;
            --fc-today-bg-color: rgba(38, 119, 184, 0.025);
            --fc-now-indicator-color: #ef4444;
        }

        .fc {
            font-family: inherit;
        }

        /* Modern Flat Toolbar Header */
        .fc .fc-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .fc .fc-toolbar-title {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            color: var(--text-main) !important;
            letter-spacing: -0.01em;
        }

        /* Button Groups & Flat Styling */
        .fc .fc-button-group {
            background: var(--gray-50);
            padding: 3px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            gap: 2px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        .fc .fc-button {
            background: transparent !important;
            border: none !important;
            color: var(--text-muted) !important;
            font-weight: 600 !important;
            font-size: 0.85rem !important;
            padding: 0.45rem 0.9rem !important;
            border-radius: 7px !important;
            box-shadow: none !important;
            transition: all 0.18s ease !important;
            text-transform: capitalize !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .fc .fc-button:hover {
            color: var(--text-main) !important;
            background: var(--gray-200) !important;
        }

        .fc .fc-button-active,
        .fc .fc-button-primary:not(:disabled).fc-button-active {
            background: var(--white) !important;
            color: var(--secondary-blue) !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.06), 0 0 0 1px rgba(0, 0, 0, 0.04) !important;
            font-weight: 700 !important;
        }

        .fc .fc-today-button {
            background: var(--white) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-main) !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            padding: 0.45rem 0.9rem !important;
            margin-right: 0.5rem !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
        }

        .fc .fc-today-button:hover:not(:disabled) {
            background: var(--gray-50) !important;
            border-color: var(--gray-300) !important;
        }

        .fc .fc-today-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Navigation Icon Buttons */
        .fc .fc-prev-button,
        .fc .fc-next-button {
            width: 32px;
            height: 32px;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 6px !important;
        }

        /* Calendar Grid and Borders */
        .fc-theme-standard .fc-scrollgrid {
            border: 1px solid var(--border-color) !important;
            border-radius: 12px;
            overflow: hidden;
        }

        .fc-theme-standard th {
            border-color: var(--border-color) !important;
            background: var(--gray-25);
            padding: 0.75rem 0.25rem !important;
        }

        .fc-theme-standard td {
            border-color: #f1f5f9 !important;
        }

        /* Modern Day Headers */
        .fc-col-header-cell {
            padding: 0 !important;
        }

        .fc-col-header-cell.fc-day-today {
            background: rgba(38, 119, 184, 0.04) !important;
            border-top: 3px solid var(--secondary-blue) !important;
        }

        .cal-header-day-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.25rem;
            gap: 0.2rem;
            user-select: none;
        }

        .cal-header-weekday {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .cal-header-daynum {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-main);
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .cal-header-day-box.is-today .cal-header-weekday {
            color: var(--secondary-blue);
            font-weight: 800;
        }

        .cal-header-day-box.is-today .cal-header-daynum {
            background: var(--secondary-blue);
            color: #ffffff;
            box-shadow: 0 3px 8px rgba(38, 119, 184, 0.3);
        }

        /* Timeline Slot Labels (8 AM, 9 AM...) */
        .fc .fc-timegrid-slot-label-cushion {
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            color: #94a3b8 !important;
            padding: 0 0.5rem !important;
            text-transform: uppercase;
        }

        .fc .fc-timegrid-slot {
            height: 3rem !important;
        }

        .fc-timegrid-slot-minor {
            border-top-style: dotted !important;
            border-top-color: #f8fafc !important;
        }

        /* Dynamic Current Time Indicator Line */
        .fc .fc-timegrid-now-indicator-line {
            border-color: #ef4444 !important;
            border-width: 2px !important;
            z-index: 10 !important;
            position: absolute;
        }

        .fc .fc-timegrid-now-indicator-line::before {
            content: '';
            position: absolute;
            left: -5px;
            top: -4px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.25);
        }

        .fc .fc-timegrid-now-indicator-arrow {
            border-color: #ef4444 !important;
            border-width: 5px 0 5px 6px !important;
            border-left-color: #ef4444 !important;
            margin-top: -5px !important;
        }

        /* Modern Event Card Styling */
        .fc-timegrid-event-harness {
            margin: 0 2px !important;
        }

        .fc-v-event {
            background: transparent !important;
            border: none !important;
            border-radius: 8px !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05) !important;
            overflow: hidden !important;
            transition: transform 0.15s ease, box-shadow 0.15s ease !important;
            cursor: pointer !important;
        }

        .fc-v-event:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08) !important;
            z-index: 20 !important;
        }

        .cal-event-card {
            width: 100%;
            height: 100%;
            padding: 0.5rem 0.65rem;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 0.2rem;
            box-sizing: border-box;
            border-left-width: 4px;
            border-left-style: solid;
            overflow: hidden;
        }

        /* Status-specific Pastel Tints */
        .cal-event-status-scheduled,
        .cal-event-status-confirmed {
            background: #f0f7ff;
            border-left-color: var(--secondary-blue);
            color: #1e3a8a;
        }

        .cal-event-status-completed {
            background: #f0fdf4;
            border-left-color: #10b981;
            color: #064e3b;
        }

        .cal-event-status-pending,
        .cal-event-status-pending_scheduling {
            background: #fffbeb;
            border-left-color: #f59e0b;
            color: #78350f;
        }

        .cal-event-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.35rem;
        }

        .cal-event-student {
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--text-main);
        }

        .cal-event-subject {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cal-event-time {
            font-size: 0.7rem;
            font-weight: 500;
            opacity: 0.85;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: auto;
        }

        /* Month View Event Badges */
        .fc-daygrid-event {
            border-radius: 6px !important;
            padding: 2px 6px !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            margin-bottom: 2px !important;
            border: none !important;
        }

        /* Event Details Modal */
        .cal-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            animation: fadeIn 0.2s ease-out;
        }

        .cal-modal-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .cal-modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            background: var(--gray-25);
        }

        .cal-status-pill {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            background: rgba(38, 119, 184, 0.1);
            color: var(--secondary-blue);
            margin-bottom: 0.35rem;
        }

        .cal-modal-title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .cal-modal-close-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.1rem;
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }

        .cal-modal-close-btn:hover {
            background: var(--gray-200);
            color: var(--text-main);
        }

        .cal-modal-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.15rem;
        }

        .cal-detail-row {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .cal-detail-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--gray-100);
            color: var(--gray-600);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .cal-detail-content {
            display: flex;
            flex-direction: column;
        }

        .cal-detail-label {
            font-size: 0.72rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.04em;
        }

        .cal-detail-value {
            font-size: 0.92rem;
            color: var(--text-main);
            font-weight: 600;
            margin-top: 0.1rem;
        }

        .cal-meeting-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--secondary-blue);
            color: #ffffff;
            padding: 0.45rem 0.9rem;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            margin-top: 0.35rem;
            transition: background 0.15s ease;
            width: fit-content;
        }

        .cal-meeting-btn:hover {
            background: var(--secondary-blue-hover);
        }

        .cal-modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
            background: var(--gray-25);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
        }

        .cal-footer-btn-secondary {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .cal-footer-btn-secondary:hover {
            color: var(--secondary-blue);
        }

        .cal-footer-btn-primary {
            background: var(--text-main);
            color: var(--white);
            border: none;
            padding: 0.45rem 1.1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.15s ease;
        }

        .cal-footer-btn-primary:hover {
            opacity: 0.9;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(12px) scale(0.98); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }

        /* Floating Interactive Calendar Hover Tooltip */
        .cal-hover-tooltip {
            position: fixed;
            z-index: 99999;
            pointer-events: none;
            background: #0f172a;
            color: #ffffff;
            padding: 0.5rem 0.85rem;
            border-radius: 9px;
            font-size: 0.78rem;
            font-weight: 600;
            box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.35), 0 6px 12px -4px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 0.65rem;
            max-width: 320px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            transition: opacity 0.12s cubic-bezier(0.16, 1, 0.3, 1), transform 0.12s cubic-bezier(0.16, 1, 0.3, 1);
            opacity: 0;
            transform: translateY(4px);
        }

        .cal-hover-tooltip.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .cal-hover-tooltip.tooltip-available {
            border-left: 3.5px solid #10b981;
            background: #064e3b;
            border-color: rgba(16, 185, 129, 0.35);
        }

        .cal-hover-tooltip.tooltip-booked {
            border-left: 3.5px solid #2677B8;
            background: #0f172a;
            border-color: rgba(38, 119, 184, 0.35);
        }

        .cal-hover-tooltip.tooltip-offhours {
            border-left: 3.5px solid #94a3b8;
            background: #1e293b;
            border-color: rgba(148, 163, 184, 0.25);
        }

        .cal-hover-tooltip.tooltip-blocked {
            border-left: 3.5px solid #ef4444;
            background: #450a0a;
            border-color: rgba(239, 68, 68, 0.35);
        }

        @media (max-width: 768px) {
            .calendar-page-container {
                padding: 1rem 0.75rem;
            }
            .summary-quick-tip {
                margin-left: 0;
                width: 100%;
            }
            .fc .fc-toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            .fc .fc-toolbar-title {
                text-align: center;
            }
            .cal-hover-tooltip {
                display: none !important;
            }
        }
    </style>

    <!-- FullCalendar JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('fullCalendar');
            const events = {!! json_encode($allEvents ?? $events) !!};
            const businessHours = {!! json_encode($businessHours ?? []) !!};

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                slotMinTime: '07:00:00',
                slotMaxTime: '22:00:00',
                slotDuration: '01:00:00',
                allDaySlot: false,
                expandRows: true,
                nowIndicator: true,
                businessHours: businessHours.length > 0 ? businessHours : false,
                selectConstraint: 'businessHours',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'Today',
                    dayGridMonth: 'Month',
                    timeGridWeek: 'Week',
                    timeGridDay: 'Day'
                },
                dayHeaderFormat: { weekday: 'short', day: 'numeric', omitCommas: true },
                dayHeaderContent: function(arg) {
                    const date = arg.date;
                    const weekday = date.toLocaleDateString(undefined, { weekday: 'short' });
                    const dayNum = date.getDate();
                    const isToday = arg.isToday;

                    const container = document.createElement('div');
                    container.className = `cal-header-day-box ${isToday ? 'is-today' : ''}`;
                    container.innerHTML = `
                        <span class="cal-header-weekday">${weekday}</span>
                        <span class="cal-header-daynum">${dayNum}</span>
                    `;
                    return { domNodes: [container] };
                },
                events: events,
                eventContent: function(arg) {
                    if (arg.event.display === 'background') {
                        return;
                    }

                    const eventObj = arg.event;
                    const props = eventObj.extendedProps || {};
                    const status = (props.status || 'scheduled').toLowerCase();

                    // Format time range cleanly
                    let timeText = arg.timeText;
                    if (!timeText && eventObj.start) {
                        const start = new Date(eventObj.start);
                        const end = eventObj.end ? new Date(eventObj.end) : null;
                        const sStr = start.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
                        const eStr = end ? end.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }) : '';
                        timeText = eStr ? `${sStr} - ${eStr}` : sStr;
                    }

                    const card = document.createElement('div');
                    card.className = `cal-event-card cal-event-status-${status}`;
                    card.innerHTML = `
                        <div class="cal-event-header">
                            <span class="cal-event-student">${props.student_name || eventObj.title || 'Student'}</span>
                        </div>
                        <span class="cal-event-subject">${props.subject_name || 'Session'}</span>
                        <div class="cal-event-time">
                            <i class="fa-regular fa-clock" style="font-size: 0.65rem;"></i>
                            <span>${timeText}</span>
                        </div>
                    `;
                    return { domNodes: [card] };
                },
                eventDidMount: function(info) {
                    if (info.event.display === 'background') {
                        info.el.setAttribute('title', 'Blocked Date (Unavailable for bookings)');
                        return;
                    }
                    const props = info.event.extendedProps || {};
                    const student = props.student_name || 'Student';
                    const subject = props.subject_name || 'Tuition Session';
                    const status = (props.status || 'Scheduled').toUpperCase().replace('_', ' ');
                    info.el.setAttribute('title', `${student} (${subject}) • Status: ${status}`);
                },
                eventClick: function(info) {
                    if (info.event.display === 'background' || (info.event.extendedProps && info.event.extendedProps.isBlocked)) {
                        return;
                    }
                    info.jsEvent.preventDefault();
                    showEventModal(info.event);
                }
            });

            calendar.render();

            // Interactive Hover Tooltip Tracking
            const tooltip = document.getElementById('calendarHoverTooltip');

            function positionTooltip(e) {
                const offset = 14;
                let left = e.clientX + offset;
                let top = e.clientY + offset;

                tooltip.style.left = `${left}px`;
                tooltip.style.top = `${top}px`;

                // Boundary collision checking
                const rect = tooltip.getBoundingClientRect();
                if (left + rect.width > window.innerWidth - 12) {
                    tooltip.style.left = `${e.clientX - rect.width - offset}px`;
                }
                if (top + rect.height > window.innerHeight - 12) {
                    tooltip.style.top = `${e.clientY - rect.height - offset}px`;
                }
            }

            function showTooltip(html, typeClass, e) {
                tooltip.className = `cal-hover-tooltip visible ${typeClass}`;
                tooltip.innerHTML = html;
                tooltip.style.display = 'flex';
                positionTooltip(e);
            }

            function hideTooltip() {
                tooltip.className = 'cal-hover-tooltip';
                tooltip.style.display = 'none';
            }

            calendarEl.addEventListener('mousemove', function(e) {
                // 1. Check if hovering over booked event card
                const eventEl = e.target.closest('.fc-event:not(.fc-bg-event)');
                if (eventEl) {
                    const student = eventEl.querySelector('.cal-event-student')?.textContent?.trim() || 'Booked Session';
                    const subject = eventEl.querySelector('.cal-event-subject')?.textContent?.trim() || 'Tuition';
                    const time = eventEl.querySelector('.cal-event-time span')?.textContent?.trim() || '';
                    
                    let statusBadge = 'Scheduled';
                    let statusIcon = 'fa-solid fa-graduation-cap';
                    let statusColor = '#60a5fa';

                    if (eventEl.querySelector('.cal-event-status-completed')) {
                        statusBadge = 'Completed';
                        statusColor = '#34d399';
                        statusIcon = 'fa-solid fa-circle-check';
                    } else if (eventEl.querySelector('.cal-event-status-pending')) {
                        statusBadge = 'Pending Scheduling';
                        statusColor = '#fbbf24';
                        statusIcon = 'fa-solid fa-hourglass-half';
                    }
                    
                    const html = `
                        <div style="font-size: 1.1rem; line-height: 1;"><i class="${statusIcon}" style="color: ${statusColor};"></i></div>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-weight: 700; color: #ffffff; font-size: 0.8rem;">${student} (${subject})</span>
                            <span style="font-size: 0.72rem; color: #94a3b8; margin-top: 2px;">
                                <i class="fa-regular fa-clock" style="font-size: 0.65rem;"></i> ${time} • <strong style="color: ${statusColor};">${statusBadge}</strong>
                            </span>
                        </div>
                    `;
                    showTooltip(html, 'tooltip-booked', e);
                    return;
                }

                // 2. Check if hovering over blocked date
                const blockedEl = e.target.closest('.cal-blocked-date-event, .fc-bg-event');
                if (blockedEl) {
                    const html = `
                        <i class="fa-solid fa-ban" style="color: #f87171; font-size: 0.95rem;"></i>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-weight: 700; color: #fecaca; font-size: 0.8rem;">Blocked Date</span>
                            <span style="font-size: 0.72rem; color: #fca5a5;">Marked as unavailable for student bookings</span>
                        </div>
                    `;
                    showTooltip(html, 'tooltip-blocked', e);
                    return;
                }

                // 3. Check if hovering over off-hours / non-business
                const nonBusinessEl = e.target.closest('.fc-non-business, .fc-day-disabled');
                if (nonBusinessEl) {
                    const html = `
                        <i class="fa-regular fa-moon" style="color: #94a3b8; font-size: 0.95rem;"></i>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-weight: 700; color: #e2e8f0; font-size: 0.8rem;">Off-Hours</span>
                            <span style="font-size: 0.72rem; color: #94a3b8;">Outside your set weekly availability schedule</span>
                        </div>
                    `;
                    showTooltip(html, 'tooltip-offhours', e);
                    return;
                }

                // 4. Check if hovering over active available working grid slots
                const gridSlot = e.target.closest('.fc-timegrid-col-frame, .fc-daygrid-day-frame, .fc-timegrid-slot-lane');
                if (gridSlot) {
                    const html = `
                        <i class="fa-solid fa-circle-check" style="color: #34d399; font-size: 0.95rem;"></i>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-weight: 700; color: #d1fae5; font-size: 0.8rem;">Available Working Hours</span>
                            <span style="font-size: 0.72rem; color: #a7f3d0;">Open slot for student bookings</span>
                        </div>
                    `;
                    showTooltip(html, 'tooltip-available', e);
                    return;
                }

                hideTooltip();
            });

            calendarEl.addEventListener('mouseleave', hideTooltip);
        });

        function showEventModal(eventObj) {
            const props = eventObj.extendedProps || {};
            const modal = document.getElementById('eventDetailModal');

            document.getElementById('modalStudentName').textContent = props.student_name || eventObj.title || 'Student Session';
            document.getElementById('modalSubject').textContent = props.subject_name || 'Tuition Session';

            const status = (props.status || 'scheduled').toUpperCase();
            const badge = document.getElementById('modalStatusBadge');
            badge.textContent = status.replace('_', ' ');

            if (status === 'COMPLETED') {
                badge.style.background = 'rgba(16, 185, 129, 0.1)';
                badge.style.color = '#10b981';
            } else if (status.includes('PENDING')) {
                badge.style.background = 'rgba(245, 158, 11, 0.1)';
                badge.style.color = '#f59e0b';
            } else {
                badge.style.background = 'rgba(38, 119, 184, 0.1)';
                badge.style.color = 'var(--secondary-blue)';
            }

            // Time string
            let timeStr = 'Scheduled Slot';
            if (eventObj.start) {
                const s = new Date(eventObj.start);
                const e = eventObj.end ? new Date(eventObj.end) : null;
                const datePart = s.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                const startTime = s.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
                const endTime = e ? e.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }) : '';
                timeStr = `${datePart} • ${startTime} ${endTime ? '- ' + endTime : ''}`;
            }
            document.getElementById('modalTime').textContent = timeStr;

            // Meeting Link
            const meetingRow = document.getElementById('modalMeetingRow');
            const meetingLink = document.getElementById('modalMeetingLink');
            if (props.meeting_link) {
                meetingLink.href = props.meeting_link;
                meetingRow.style.display = 'flex';
            } else {
                meetingRow.style.display = 'none';
            }

            modal.style.display = 'flex';
        }

        function hideEventModal() {
            const modal = document.getElementById('eventDetailModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        function closeEventModal(e) {
            if (e.target && e.target.id === 'eventDetailModal') {
                hideEventModal();
            }
        }
    </script>
@endsection

