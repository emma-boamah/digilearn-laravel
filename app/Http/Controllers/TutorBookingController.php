<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TutorBookingController extends Controller
{
    /**
     * Display list of bookings grouped by status tabs.
     */
    public function incoming(Request $request)
    {
        $user = Auth::user();

        $tab = $request->input('tab', 'pending');

        $query = Booking::with(['student', 'subject'])
            ->where('tutor_id', $user->id);

        if ($tab === 'pending') {
            $query->where('status', 'pending_scheduling');
        } elseif ($tab === 'upcoming') {
            $query->whereIn('status', ['scheduled', 'confirmed']);
        } elseif ($tab === 'completed') {
            $query->where('status', 'completed');
        } elseif ($tab === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(12);

        $counts = [
            'pending' => Booking::where('tutor_id', $user->id)->where('status', 'pending_scheduling')->count(),
            'upcoming' => Booking::where('tutor_id', $user->id)->whereIn('status', ['scheduled', 'confirmed'])->count(),
            'completed' => Booking::where('tutor_id', $user->id)->where('status', 'completed')->count(),
            'cancelled' => Booking::where('tutor_id', $user->id)->where('status', 'cancelled')->count(),
        ];

        return view('tutors.bookings', compact('user', 'bookings', 'tab', 'counts'));
    }

    /**
     * Accept a pending booking request and set meeting link & start time.
     */
    public function accept(Request $request, $id)
    {
        $user = Auth::user();

        $booking = Booking::where('id', $id)
            ->where('tutor_id', $user->id)
            ->firstOrFail();

        $request->validate([
            'start_time' => 'nullable|date',
            'meeting_link' => 'required|url',
        ]);

        $updateData = [
            'status' => 'confirmed',
            'meeting_link' => $request->meeting_link,
        ];

        if ($request->filled('start_time')) {
            $startTime = \Carbon\Carbon::parse($request->start_time);
            $updateData['start_time'] = $startTime;
            $updateData['end_time'] = $startTime->copy()->addHour();
        }

        $booking->update($updateData);

        return back()->with('success', 'Booking accepted and student notified with meeting details.');
    }

    /**
     * Decline a booking request and refund student's credits.
     */
    public function decline(Request $request, $id)
    {
        $user = Auth::user();

        $booking = Booking::where('id', $id)
            ->where('tutor_id', $user->id)
            ->firstOrFail();

        if ($booking->status === 'completed') {
            return back()->with('error', 'Cannot decline a completed session.');
        }

        $request->validate([
            'decline_reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $booking->update([
                'status' => 'cancelled',
                'decline_reason' => $request->decline_reason,
                'cancelled_by' => $user->id,
                'cancellation_reason' => 'Declined by tutor: ' . $request->decline_reason,
            ]);

            // Refund escrow credits back to student balance
            $student = User::find($booking->student_id);
            if ($student) {
                $student->increment('credit_balance', $booking->credits_paid);
            }

            DB::commit();
            return back()->with('success', 'Booking declined. Full credit amount refunded to the student.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process booking decline.');
        }
    }

    /**
     * Propose a new date/time for session.
     */
    public function reschedule(Request $request, $id)
    {
        $user = Auth::user();

        $booking = Booking::where('id', $id)
            ->where('tutor_id', $user->id)
            ->firstOrFail();

        $request->validate([
            'new_start_time' => 'required|date|after:now',
        ]);

        $startTime = \Carbon\Carbon::parse($request->new_start_time);
        $booking->update([
            'start_time' => $startTime,
            'end_time' => $startTime->copy()->addHour(),
            'status' => 'scheduled',
        ]);

        return back()->with('success', 'Session time rescheduled successfully.');
    }

    /**
     * Save private tutor notes for a booking session.
     */
    public function sessionNotes(Request $request, $id)
    {
        $user = Auth::user();

        $booking = Booking::where('id', $id)
            ->where('tutor_id', $user->id)
            ->firstOrFail();

        $request->validate([
            'tutor_notes' => 'required|string|max:2000',
        ]);

        $booking->update([
            'tutor_notes' => $request->tutor_notes,
        ]);

        return back()->with('success', 'Session notes updated.');
    }

    /**
     * Session history log.
     */
    public function history()
    {
        $user = Auth::user();

        $completedBookings = Booking::with(['student', 'subject'])
            ->where('tutor_id', $user->id)
            ->where('status', 'completed')
            ->latest('updated_at')
            ->paginate(15);

        return view('tutors.history', compact('user', 'completedBookings'));
    }
}
