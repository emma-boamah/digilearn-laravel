<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\TutorPayout;
use App\Models\PlatformSetting;
use App\Services\PaystackService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TutorEarningsController extends Controller
{
    protected PaystackService $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }

    /**
     * Display earnings overview and statistics.
     */
    public function index()
    {
        $user = Auth::user();
        $tutorProfile = $user->tutorProfile;

        if (!$tutorProfile || !$tutorProfile->is_approved) {
            return redirect()->route('tutors.dashboard')->with('error', 'Approved tutor profile required.');
        }

        // Available balance is the user's current credit balance
        $availableBalance = (float) $user->credit_balance;

        // Pending escrow balance: sum of (credits_paid - commission_amount) for active/scheduled bookings
        $pendingEscrow = Booking::where('tutor_id', $user->id)
            ->whereIn('status', ['pending_scheduling', 'scheduled', 'confirmed'])
            ->selectRaw('SUM(credits_paid - commission_amount) as total')
            ->value('total') ?? 0.00;

        // Total lifetime earnings: sum of completed booking payouts
        $lifetimeEarnings = Booking::where('tutor_id', $user->id)
            ->where('status', 'completed')
            ->selectRaw('SUM(credits_paid - commission_amount) as total')
            ->value('total') ?? 0.00;

        // Recent completed session earnings
        $recentEarnings = Booking::with(['student', 'subject'])
            ->where('tutor_id', $user->id)
            ->where('status', 'completed')
            ->latest('updated_at')
            ->take(10)
            ->get();

        // Monthly earnings chart data for last 6 months
        $monthlyEarnings = Booking::where('tutor_id', $user->id)
            ->where('status', 'completed')
            ->where('updated_at', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(updated_at, "%Y-%m") as month_key, SUM(credits_paid - commission_amount) as total')
            ->groupBy('month_key')
            ->orderBy('month_key', 'asc')
            ->pluck('total', 'month_key')
            ->toArray();

        $chartLabels = [];
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');
            $chartLabels[] = $date->format('M Y');
            $chartData[] = (float) ($monthlyEarnings[$key] ?? 0);
        }

        $minPayoutAmount = PlatformSetting::getValue('min_payout_amount', 50.00);

        return view('tutors.earnings', compact(
            'user',
            'tutorProfile',
            'availableBalance',
            'pendingEscrow',
            'lifetimeEarnings',
            'recentEarnings',
            'chartLabels',
            'chartData',
            'minPayoutAmount'
        ));
    }

    /**
     * Display detailed transaction history.
     */
    public function transactions(Request $request)
    {
        $user = Auth::user();

        $completedBookings = Booking::with(['student', 'subject'])
            ->where('tutor_id', $user->id)
            ->where('status', 'completed')
            ->latest('updated_at')
            ->paginate(15, ['*'], 'earnings_page');

        $payouts = TutorPayout::where('tutor_id', $user->id)
            ->latest()
            ->paginate(15, ['*'], 'payouts_page');

        return view('tutors.transactions', compact('user', 'completedBookings', 'payouts'));
    }

    /**
     * Request a payout.
     */
    public function requestPayout(Request $request)
    {
        $user = Auth::user();
        $tutorProfile = $user->tutorProfile;

        if (!$tutorProfile || !$tutorProfile->is_approved) {
            return back()->with('error', 'Only approved tutors can request payouts.');
        }

        $minPayoutAmount = PlatformSetting::getValue('min_payout_amount', 50.00);

        $request->validate([
            'amount' => "required|numeric|min:{$minPayoutAmount}",
        ]);

        $requestedAmount = (float) $request->amount;

        if ($user->credit_balance < $requestedAmount) {
            return back()->with('error', "Insufficient credit balance. You currently have GHS {$user->credit_balance}.");
        }

        $payoutMethod = $tutorProfile->payout_method;
        if (!$payoutMethod) {
            return back()->with('error', 'Please configure your payout method in your profile settings before requesting a withdrawal.');
        }

        $reference = 'POT-' . strtoupper(Str::random(10));

        DB::beginTransaction();
        try {
            // Deduct balance immediately (Escrow to Payout pending state)
            $user->decrement('credit_balance', $requestedAmount);

            $bankName = $payoutMethod === 'momo' ? $tutorProfile->payout_momo_network : $tutorProfile->payout_bank_name;
            $accountNumber = $payoutMethod === 'momo' ? $tutorProfile->payout_momo_number : $tutorProfile->payout_bank_account_number;

            $payout = TutorPayout::create([
                'tutor_id' => $user->id,
                'amount' => $requestedAmount,
                'status' => 'pending',
                'payout_method' => $payoutMethod,
                'reference' => $reference,
                'bank_name' => $bankName,
                'account_number' => $accountNumber,
            ]);

            // Attempt Paystack automated transfer if Paystack keys are set
            try {
                // Determine recipient network/bank code map if available
                $bankCode = match (strtoupper($bankName ?? '')) {
                    'MTN' => 'MTN',
                    'TELECEL', 'VODAFONE' => 'VOD',
                    'AT', 'AIRTELTIGO' => 'ATL',
                    default => '000',
                };

                $type = $payoutMethod === 'momo' ? 'mobile_money' : 'ghipss';
                $name = $tutorProfile->legal_name ?? $user->name;

                $recipientResponse = $this->paystackService->createTransferRecipient(
                    $name,
                    $accountNumber ?? '',
                    $bankCode,
                    $type
                );

                if (isset($recipientResponse['data']['recipient_code'])) {
                    $recipientCode = $recipientResponse['data']['recipient_code'];
                    $payout->paystack_recipient_code = $recipientCode;

                    $transferResponse = $this->paystackService->initiateTransfer(
                        $requestedAmount,
                        $recipientCode,
                        "Tutor payout for session earnings - {$reference}",
                        $reference
                    );

                    if (isset($transferResponse['data']['transfer_code'])) {
                        $payout->paystack_transfer_code = $transferResponse['data']['transfer_code'];
                        $payout->status = 'processing';
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Paystack transfer automatic initiation skipped or failed: ' . $e->getMessage());
                // Fallback to manual admin approval/processing without failing the user request
            }

            $payout->save();
            DB::commit();

            return back()->with('success', "Payout request of GHS {$requestedAmount} submitted successfully. Status: " . ucfirst($payout->status));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payout request error: ' . $e->getMessage());
            return back()->with('error', 'Failed to submit payout request. Please try again.');
        }
    }

    /**
     * Display payout history.
     */
    public function payoutHistory()
    {
        $user = Auth::user();
        $payouts = TutorPayout::where('tutor_id', $user->id)->latest()->paginate(15);

        return view('tutors.payouts', compact('user', 'payouts'));
    }
}
