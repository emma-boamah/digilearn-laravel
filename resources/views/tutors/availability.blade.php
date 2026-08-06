@extends('layouts.tutors-layout')

@section('sidebar')
    @include('components.tutors-sidebar')
@endsection

@section('filter-bar')
    <div style="flex: 1; display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0;">
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-main);">Weekly Availability & Hybrid Scheduling</h2>
    </div>
@endsection

@section('content')
    <div style="padding: 2rem; max-width: 1000px; margin: 0 auto;">
        @if(session('success'))
            <div style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; border: 1px solid #a7f3d0;">
                <i class="fa-solid fa-circle-check" style="margin-right: 0.5rem;"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Scheduling Mode Banner -->
        <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.5rem; margin-bottom: 2rem; box-shadow: var(--shadow-sm);">
            @if($tutorProfile->scheduling_preference === 'external')
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-arrow-up-right-from-square" style="color: #f59e0b; margin-right: 0.35rem;"></i> External Scheduling Active
                </h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.75rem;">
                    Your primary scheduling method is <strong>external</strong> via: <a href="{{ $tutorProfile->scheduling_link }}" target="_blank" style="color: var(--secondary-blue); font-weight: 600;">{{ $tutorProfile->scheduling_link }}</a>
                </p>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0;">
                    <i class="fa-solid fa-lightbulb" style="color: #f59e0b; margin-right: 0.25rem;"></i> <strong>Tip:</strong> You can still set up in-app availability hours below as a complementary option. Students will see both options on your profile.
                </p>
            @else
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-calendar-check" style="color: var(--secondary-blue); margin-right: 0.35rem;"></i> DigiLearn In-App Calendar — Active
                </h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0;">
                    Students will book sessions directly through your weekly availability slots below. Set your recurring hours, block holidays, and DigiLearn handles the rest — timezone syncing, confirmations, and reminders.
                </p>
            @endif
        </div>

        <form action="{{ route('tutors.schedule.availability.store') }}" method="POST">
            @csrf
            
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 2rem; padding: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin: 0;">Weekly Recurring Availability Hours</h3>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-main);">Session Slot Duration:</label>
                        <select name="slot_duration_minutes" style="padding: 0.35rem 0.65rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-weight: 600;">
                            <option value="30">30 Mins</option>
                            <option value="45">45 Mins</option>
                            <option value="60" selected>60 Mins (1 Hour)</option>
                            <option value="90">90 Mins</option>
                        </select>
                    </div>
                </div>

                @php
                    $days = [
                        1 => 'Monday',
                        2 => 'Tuesday',
                        3 => 'Wednesday',
                        4 => 'Thursday',
                        5 => 'Friday',
                        6 => 'Saturday',
                        0 => 'Sunday'
                    ];
                @endphp

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($days as $dayIndex => $dayName)
                        @php
                            $slot = $availabilities[$dayIndex] ?? null;
                            $enabled = $slot ? true : false;
                            $startTime = $slot ? $slot->start_time : '09:00';
                            $endTime = $slot ? $slot->end_time : '17:00';
                        @endphp
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1.25rem; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main);">
                            <div style="width: 140px; display: flex; align-items: center; gap: 0.65rem;">
                                <input type="checkbox" name="slots[{{ $dayIndex }}][enabled]" value="1" @checked($enabled) style="width: 18px; height: 18px;">
                                <input type="hidden" name="slots[{{ $dayIndex }}][day_of_week]" value="{{ $dayIndex }}">
                                <span style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">{{ $dayName }}</span>
                            </div>

                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="display: flex; align-items: center; gap: 0.35rem;">
                                    <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">From:</span>
                                    <input type="time" name="slots[{{ $dayIndex }}][start_time]" value="{{ $startTime }}" style="padding: 0.35rem 0.65rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-main); font-weight: 600;">
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.35rem;">
                                    <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">To:</span>
                                    <input type="time" name="slots[{{ $dayIndex }}][end_time]" value="{{ $endTime }}" style="padding: 0.35rem 0.65rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-main); font-weight: 600;">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="text-align: right; margin-top: 1.5rem;">
                    <button type="submit" style="background: var(--secondary-blue); color: white; border: none; padding: 0.75rem 1.75rem; border-radius: 8px; font-weight: 700; cursor: pointer;">
                        <i class="fa-solid fa-floppy-disk" style="margin-right: 0.35rem;"></i> Save Weekly Schedule
                    </button>
                </div>
            </div>
        </form>

        <!-- Date Override / Blocked Dates Card -->
        <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; padding: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                <i class="fa-solid fa-ban" style="color: var(--primary-red); margin-right: 0.35rem;"></i> Block Specific Dates (Holidays / Personal Days)
            </h3>

            <form action="{{ route('tutors.schedule.block') }}" method="POST" style="display: flex; gap: 1rem; align-items: flex-end; margin-bottom: 1.5rem;">
                @csrf
                <div style="flex: 1;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Select Date to Block</label>
                    <input type="date" name="specific_date" required min="{{ date('Y-m-d') }}" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                </div>
                <button type="submit" style="background: var(--primary-red); color: white; border: none; padding: 0.65rem 1.25rem; border-radius: 8px; font-weight: 700; cursor: pointer;">
                    Block Date
                </button>
            </form>

            @if(isset($blockedDates) && count($blockedDates) > 0)
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    @foreach($blockedDates as $blocked)
                        <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--gray-100); border: 1px solid var(--border-color); padding: 0.4rem 0.85rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; color: var(--text-main);">
                            <span><i class="fa-solid fa-calendar-xmark" style="color: var(--primary-red);"></i> {{ $blocked->specific_date ? $blocked->specific_date->format('M d, Y') : '' }}</span>
                            <form action="{{ route('tutors.schedule.unblock') }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="id" value="{{ $blocked->id }}">
                                <button type="submit" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 0.85rem;">&times;</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
