<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TutorAvailability;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class TutorScheduleController extends Controller
{
    /**
     * Show availability configuration page.
     */
    public function availability()
    {
        $user = Auth::user();
        $tutorProfile = $user->tutorProfile;

        if (!$tutorProfile || !$tutorProfile->is_approved) {
            return redirect()->route('tutors.dashboard')->with('error', 'Approved tutor profile required.');
        }

        $availabilities = TutorAvailability::where('tutor_id', $user->id)
            ->where('is_recurring', true)
            ->get()
            ->keyBy('day_of_week');

        $blockedDates = TutorAvailability::where('tutor_id', $user->id)
            ->where('is_blocked', true)
            ->where('specific_date', '>=', now()->toDateString())
            ->orderBy('specific_date', 'asc')
            ->get();

        return view('tutors.availability', compact('user', 'tutorProfile', 'availabilities', 'blockedDates'));
    }

    /**
     * Save weekly recurring availability hours.
     */
    public function storeAvailability(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'slots' => 'nullable|array',
            'slots.*.day_of_week' => 'required|integer|between:0,6',
            'slots.*.start_time' => 'required',
            'slots.*.end_time' => 'required',
            'slot_duration_minutes' => 'required|integer|in:30,45,60,90',
        ]);

        $slotDuration = (int) $request->input('slot_duration_minutes', 60);
        $slotsData = $request->input('slots', []);

        // Delete existing recurring slots and re-insert
        TutorAvailability::where('tutor_id', $user->id)
            ->where('is_recurring', true)
            ->delete();

        foreach ($slotsData as $slot) {
            if (isset($slot['enabled']) && $slot['enabled']) {
                TutorAvailability::create([
                    'tutor_id' => $user->id,
                    'day_of_week' => (int) $slot['day_of_week'],
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'is_recurring' => true,
                    'slot_duration_minutes' => $slotDuration,
                ]);
            }
        }

        return back()->with('success', 'Weekly availability schedule updated successfully.');
    }

    /**
     * Block a specific date.
     */
    public function blockDate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'specific_date' => 'required|date|after_or_equal:today',
        ]);

        TutorAvailability::create([
            'tutor_id' => $user->id,
            'specific_date' => $request->specific_date,
            'start_time' => '00:00:00',
            'end_time' => '23:59:59',
            'is_recurring' => false,
            'is_blocked' => true,
        ]);

        return back()->with('success', "Blocked date {$request->specific_date} successfully.");
    }

    /**
     * Remove a date block.
     */
    public function unblockDate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'id' => 'required|exists:tutor_availabilities,id',
        ]);

        TutorAvailability::where('id', $request->id)
            ->where('tutor_id', $user->id)
            ->delete();

        return back()->with('success', 'Date block removed.');
    }

    /**
     * Show interactive calendar view overlaying bookings on availability.
     */
    public function calendar()
    {
        $user = Auth::user();
        $tutorProfile = $user->tutorProfile;

        if (!$tutorProfile || !$tutorProfile->is_approved) {
            return redirect()->route('tutors.dashboard')->with('error', 'Approved tutor profile required.');
        }

        $bookings = Booking::with(['student', 'subject'])
            ->where('tutor_id', $user->id)
            ->whereIn('status', ['pending_scheduling', 'scheduled', 'confirmed', 'completed'])
            ->get();

        $events = $bookings->map(function ($booking) {
            $title = ($booking->student->name ?? 'Student') . ' - ' . ($booking->subject->name ?? 'Session');
            $color = match ($booking->status) {
                'completed' => '#10b981',
                'scheduled', 'confirmed' => '#2677B8',
                default => '#f59e0b',
            };

            return [
                'id' => $booking->id,
                'title' => $title,
                'start' => $booking->start_time ? $booking->start_time->toIso8601String() : null,
                'end' => $booking->end_time ? $booking->end_time->toIso8601String() : null,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'status' => $booking->status,
                'student_name' => $booking->student->name ?? 'Student',
                'subject_name' => $booking->subject->name ?? 'Session',
                'meeting_link' => $booking->meeting_link,
            ];
        })->filter(fn ($e) => !empty($e['start']));

        return view('tutors.calendar', compact('user', 'tutorProfile', 'events'));
    }

    /**
     * JSON endpoint for fetching available time slots for a given tutor (used by booking checkout UI).
     */
    public function apiSlots($tutorId)
    {
        $recurringAvailabilities = TutorAvailability::where('tutor_id', $tutorId)
            ->where('is_recurring', true)
            ->get();

        $blockedDates = TutorAvailability::where('tutor_id', $tutorId)
            ->where('is_blocked', true)
            ->pluck('specific_date')
            ->map(fn ($d) => $d ? $d->format('Y-m-d') : null)
            ->toArray();

        return response()->json([
            'recurring' => $recurringAvailabilities,
            'blocked_dates' => array_filter($blockedDates),
        ]);
    }
}
