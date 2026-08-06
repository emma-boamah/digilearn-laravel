@extends('layouts.tutors-layout')

@section('sidebar')
    @include('components.tutors-sidebar')
@endsection

@section('filter-bar')
    <div style="flex: 1; display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0;">
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-main);">Booking Request Manager</h2>
    </div>
@endsection

@section('content')
    <div style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
        @if(session('success'))
            <div style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; border: 1px solid #a7f3d0;">
                <i class="fa-solid fa-circle-check" style="margin-right: 0.5rem;"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; border: 1px solid #fca5a5;">
                <i class="fa-solid fa-circle-exclamation" style="margin-right: 0.5rem;"></i> {{ session('error') }}
            </div>
        @endif

        <!-- Tab Navigation -->
        <div style="display: flex; gap: 0.75rem; border-bottom: 1px solid var(--border-color); margin-bottom: 1.5rem; padding-bottom: 0.5rem;">
            <a href="{{ route('tutors.bookings.index', ['tab' => 'pending']) }}" style="padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 0.9rem; background: {{ $tab === 'pending' ? 'var(--secondary-blue)' : 'transparent' }}; color: {{ $tab === 'pending' ? 'white' : 'var(--text-main)' }};">
                Pending Requests ({{ $counts['pending'] ?? 0 }})
            </a>
            <a href="{{ route('tutors.bookings.index', ['tab' => 'upcoming']) }}" style="padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 0.9rem; background: {{ $tab === 'upcoming' ? 'var(--secondary-blue)' : 'transparent' }}; color: {{ $tab === 'upcoming' ? 'white' : 'var(--text-main)' }};">
                Upcoming Sessions ({{ $counts['upcoming'] ?? 0 }})
            </a>
            <a href="{{ route('tutors.bookings.index', ['tab' => 'completed']) }}" style="padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 0.9rem; background: {{ $tab === 'completed' ? 'var(--secondary-blue)' : 'transparent' }}; color: {{ $tab === 'completed' ? 'white' : 'var(--text-main)' }};">
                Completed ({{ $counts['completed'] ?? 0 }})
            </a>
            <a href="{{ route('tutors.bookings.index', ['tab' => 'cancelled']) }}" style="padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 0.9rem; background: {{ $tab === 'cancelled' ? 'var(--secondary-blue)' : 'transparent' }}; color: {{ $tab === 'cancelled' ? 'white' : 'var(--text-main)' }};">
                Cancelled ({{ $counts['cancelled'] ?? 0 }})
            </a>
        </div>

        <!-- Bookings List Cards -->
        @if(isset($bookings) && count($bookings) > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 1.25rem;">
                @foreach($bookings as $booking)
                    <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.25rem; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                                <div style="display: flex; gap: 0.75rem; align-items: center;">
                                    <div style="width: 42px; height: 42px; border-radius: 50%; background: var(--secondary-blue); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem;">
                                        {{ strtoupper(substr($booking->student->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <h4 style="font-size: 1rem; font-weight: 700; margin: 0; color: var(--text-main);">{{ $booking->student->name ?? 'Student' }}</h4>
                                        <span style="font-size: 0.8rem; color: var(--text-muted);">Subject: {{ $booking->subject->name ?? 'General' }}</span>
                                    </div>
                                </div>
                                <span style="font-size: 0.85rem; font-weight: 700; color: #10b981;">GHS {{ number_format($booking->credits_paid, 2) }}</span>
                            </div>

                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem; line-height: 1.5;">
                                <div><i class="fa-regular fa-clock" style="margin-right: 0.35rem;"></i> {{ $booking->start_time ? $booking->start_time->format('M d, Y @ h:i A') : 'Time Pending' }}</div>
                                @if($booking->meeting_link)
                                    <div style="margin-top: 0.25rem;"><i class="fa-solid fa-link" style="margin-right: 0.35rem;"></i> <a href="{{ $booking->meeting_link }}" target="_blank" style="color: var(--secondary-blue);">Join Room</a></div>
                                @endif
                                @if($booking->decline_reason)
                                    <div style="color: var(--primary-red); margin-top: 0.35rem;">Reason: {{ $booking->decline_reason }}</div>
                                @endif
                            </div>
                        </div>

                        <!-- Actions based on tab -->
                        @if($tab === 'pending')
                            <div style="border-top: 1px solid var(--border-color); padding-top: 0.75rem; margin-top: 0.5rem; display: flex; gap: 0.5rem;">
                                <!-- Accept Button / Form -->
                                <form action="{{ route('tutors.bookings.accept', $booking->id) }}" method="POST" style="flex: 1;">
                                    @csrf
                                    <input type="hidden" name="meeting_link" value="{{ $tutorProfile->scheduling_link ?? 'https://meet.google.com' }}">
                                    <button type="submit" style="width: 100%; background: #10b981; color: white; border: none; padding: 0.5rem; border-radius: 6px; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                                        Accept Request
                                    </button>
                                </form>

                                <!-- Decline Button / Modal Trigger -->
                                <form action="{{ route('tutors.bookings.decline', $booking->id) }}" method="POST" style="flex: 1;" onsubmit="return confirm('Decline booking request and refund student?');">
                                    @csrf
                                    <input type="hidden" name="decline_reason" value="Schedule conflict or unavailable">
                                    <button type="submit" style="width: 100%; background: var(--primary-red); color: white; border: none; padding: 0.5rem; border-radius: 6px; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                                        Decline
                                    </button>
                                </form>
                            </div>
                        @elseif($tab === 'upcoming')
                            <div style="border-top: 1px solid var(--border-color); padding-top: 0.75rem; margin-top: 0.5rem;">
                                <form action="{{ route('bookings.complete', $booking->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" style="width: 100%; background: var(--secondary-blue); color: white; border: none; padding: 0.5rem; border-radius: 6px; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                                        Mark Session Completed & Release Credits
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            @if(method_exists($bookings, 'links'))
                <div style="margin-top: 1.5rem;">
                    {{ $bookings->links() }}
                </div>
            @endif
        @else
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 3rem; text-align: center; color: var(--text-muted);">
                <i class="fa-regular fa-folder-open" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <p style="margin: 0; font-size: 1rem;">No bookings found in this category.</p>
            </div>
        @endif
    </div>
@endsection
