<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\User;
use App\Models\TutorSubject;
use App\Services\TutorRateService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    protected TutorRateService $rateService;

    public function __construct(TutorRateService $rateService)
    {
        $this->rateService = $rateService;
    }

    /**
     * Handle the booking checkout process.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'tutor_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'duration_hours' => 'required|numeric|min:1|max:5',
        ]);

        $student = Auth::user();
        $tutorId = (int) $request->tutor_id;
        $subjectId = (int) $request->subject_id;
        $duration = (int) $request->duration_hours;

        if ($student->id == $tutorId) {
            return back()->with('error', 'You cannot book a session with yourself.');
        }

        $hasDiscount = $student->hasExtraTuitionPlan() || $student->hasAdvancedLevelAccess();
        $costDetails = $this->rateService->calculateBookingCost($tutorId, $subjectId, $duration, $hasDiscount);
        $totalCreditsRequired = $costDetails['total_credits'];

        if ($student->credit_balance < $totalCreditsRequired) {
            return back()->with('error', "Insufficient credits. You need {$totalCreditsRequired} credits but only have {$student->credit_balance}. Please top up your wallet.");
        }

        DB::beginTransaction();
        try {
            // Deduct credits from student (Escrow)
            $student->decrement('credit_balance', $totalCreditsRequired);

            // Calculate platform commission split via TutorRateService
            $split = $this->rateService->calculateCommissionSplit($totalCreditsRequired);

            $booking = Booking::create([
                'student_id' => $student->id,
                'tutor_id' => $tutorId,
                'subject_id' => $subjectId,
                'credits_paid' => $totalCreditsRequired,
                'commission_amount' => $split['platform_fee'],
                'status' => 'pending_scheduling',
            ]);

            DB::commit();

            // Redirect to tutor's scheduling link (Calendly)
            $tutor = User::with('tutorProfile')->find($tutorId);
            $schedulingLink = $tutor->tutorProfile->scheduling_link ?? '#';

            return redirect()->away($schedulingLink)
                ->with('success', 'Booking paid successfully. Please select your time slot.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred during checkout. Please try again.');
        }
    }

    /**
     * Complete the booking and release escrow to tutor.
     */
    public function complete(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $user = Auth::user();

        // Only the student or tutor can complete it
        if ($user->id !== $booking->student_id && $user->id !== $booking->tutor_id && !$user->is_superuser) {
            abort(403);
        }

        if ($booking->status === 'completed') {
            return back()->with('info', 'Booking is already completed.');
        }

        DB::beginTransaction();
        try {
            $booking->update(['status' => 'completed']);

            // Release escrow to tutor
            $tutorAmount = $booking->credits_paid - $booking->commission_amount;
            
            $tutor = User::find($booking->tutor_id);
            $tutor->increment('credit_balance', $tutorAmount);

            DB::commit();

            return back()->with('success', 'Session marked as complete. Credits released to tutor.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to complete the booking.');
        }
    }
}
