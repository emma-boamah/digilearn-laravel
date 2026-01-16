<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PricingPlan;
use App\Models\UserSubscription;
use App\Notifications\PaymentSuccessfulNotification;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected PaystackService $paystack;

    public function __construct(PaystackService $paystack)
    {
        $this->paystack = $paystack;
    }

    /**
     * Initiate payment for a pricing plan
     */
    public function initiatePayment(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:pricing_plans,slug',
            'duration' => 'required|in:month,3month,6month,12month,trial',
        ]);

        $user = Auth::user();

        // Check if user is suspended
        if ($user->isSuspended()) {
            return redirect()->route('auth.suspended');
        }

        $plan = PricingPlan::where('slug', $request->plan_id)->firstOrFail();

        // Calculate amount based on duration
        $amount = $this->calculateAmount($plan, $request->duration);

        if ($amount == 0 && $request->duration !== 'trial') {
            return back()->withErrors(['error' => 'Invalid payment amount']);
        }

        // Generate unique reference
        $reference = 'PAY-' . Str::uuid();

        // Create payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'pricing_plan_id' => $plan->id,
            'amount' => $amount,
            'currency' => $plan->currency,
            'reference' => $reference,
            'status' => 'pending',
            'metadata' => [
                'duration' => $request->duration,
                'plan_name' => $plan->name,
            ],
        ]);

        // If trial, handle directly
        if ($request->duration === 'trial') {
            return $this->handleTrialPayment($payment);
        }

        // Initialize Paystack payment
        try {
            $paystackResponse = $this->paystack->initializePayment([
                'email' => $user->email,
                'amount' => $amount * 100, // Convert to kobo
                'reference' => $reference,
                'callback_url' => route('payment.callback'),
                'metadata' => [
                    'payment_id' => $payment->id,
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'duration' => $request->duration,
                ],
            ]);

            return response()->json([
                'success' => true,
                'authorization_url' => $paystackResponse['data']['authorization_url'],
                'reference' => $reference,
            ]);
        } catch (\Exception $e) {
            Log::error('Paystack payment initialization failed', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
            ]);

            $payment->update(['status' => 'failed']);
            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Handle Paystack callback
     */
    public function callback(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return redirect()->route('pricing')->withErrors(['error' => 'Invalid payment reference']);
        }

        try {
            $paystackResponse = $this->paystack->verifyPayment($reference);

            $payment = Payment::where('reference', $reference)->first();

            if (!$payment) {
                Log::error('Payment not found for reference', ['reference' => $reference]);
                return redirect()->route('pricing')->withErrors(['error' => 'Payment record not found']);
            }

            if ($paystackResponse['data']['status'] === 'success') {
                $this->handleSuccessfulPayment($payment, $paystackResponse['data']);
                if (Auth::check()) {
                    return redirect()->route('payment.success');
                } else {
                    return redirect()->route('login')->with('success', 'Payment successful! Please login to access your account.');
                }
            } else {
                $payment->update(['status' => 'failed']);
                if (Auth::check()) {
                    return redirect()->route('payment.cancel')->withErrors(['error' => 'Payment was not successful']);
                } else {
                    return redirect()->route('pricing')->withErrors(['error' => 'Payment was not successful. Please try again.']);
                }
            }
        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('pricing')->withErrors(['error' => 'Payment verification failed']);
        }
    }

    /**
     * Handle Paystack webhook
     */
    public function webhook(Request $request)
    {
        $signature = $request->header('x-paystack-signature');

        if (!$signature) {
            return response()->json(['error' => 'No signature'], 401);
        }

        $secret = config('services.paystack.secret');
        $computedSignature = hash_hmac('sha512', $request->getContent(), $secret);

        if ($signature !== $computedSignature) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload = json_decode($request->getContent(), true);

        if ($payload['event'] === 'charge.success') {
            $reference = $payload['data']['reference'];
            $payment = Payment::where('reference', $reference)->first();

            if ($payment && $payment->status !== 'success') {
                $this->handleSuccessfulPayment($payment, $payload['data']);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Payment success page
     */
    public function success()
    {
        return view('payment.success');
    }

    /**
     * Payment cancel page
     */
    public function cancel()
    {
        return view('payment.cancel');
    }

    /**
     * Calculate payment amount based on plan and duration
     */
    private function calculateAmount(PricingPlan $plan, string $duration): float
    {
        return $plan->getPriceForDuration($duration);
    }


    /**
     * Handle trial payment
     */
    private function handleTrialPayment(Payment $payment)
    {
        $payment->update([
            'status' => 'success',
            'paid_at' => now(),
        ]);

        $this->createOrUpdateSubscription($payment, 'trial');

        return redirect()->route('payment.success');
    }

    /**
     * Handle successful payment
     */
    private function handleSuccessfulPayment(Payment $payment, array $paystackData)
    {
        // Validate that the paid amount matches the expected amount
        $paidAmount = $paystackData['amount'] / 100; // Convert from kobo to GHS
        
        // Cast both to float for comparison with tolerance for floating point errors
        $expectedAmount = (float) $payment->amount;
        $paidAmountFloat = (float) $paidAmount;

        if (!$this->amountsMatch($expectedAmount, $paidAmountFloat)) {
            Log::error('Payment amount mismatch', [
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
                'expected_amount' => $expectedAmount,
                'paid_amount' => $paidAmountFloat,
            ]);

            $payment->update(['status' => 'failed']);
            return;
        }

        $payment->update([
            'status' => 'success',
            'transaction_id' => $paystackData['id'],
            'payment_provider' => $this->extractPaymentProvider($paystackData),
            'paid_at' => now(),
        ]);

        $this->createOrUpdateSubscription($payment, 'active');

        // Send notification
        $payment->user->notify(new PaymentSuccessfulNotification([
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'transaction_id' => $payment->transaction_id,
            'reference' => $payment->reference,
            'plan_name' => $payment->pricingPlan->name ?? 'Unknown Plan',
        ]));
    }

    /**
     * Extract payment provider from Paystack data
     */
    private function extractPaymentProvider(array $paystackData): ?string
    {
        // Paystack provides channel info
        $channel = $paystackData['channel'] ?? null;

        return match ($channel) {
            'mobile_money' => $this->extractMobileMoneyProvider($paystackData),
            default => $channel,
        };
    }

    /**
     * Extract mobile money provider
     */
    private function extractMobileMoneyProvider(array $paystackData): ?string
    {
        // Check authorization data for provider info
        $authorization = $paystackData['authorization'] ?? [];

        if (isset($authorization['mobile_money_provider'])) {
            return $authorization['mobile_money_provider'];
        }

        // Fallback to checking bank name or other fields
        return $authorization['bank'] ?? 'Mobile Money';
    }

    /**
     * Create or update user subscription
     */
    private function createOrUpdateSubscription(Payment $payment, string $status)
    {
        $duration = $payment->metadata['duration'] ?? 'month';
        $expiresAt = $this->calculateExpiryDate($duration);

        UserSubscription::updateOrCreate(
            ['user_id' => $payment->user_id, 'pricing_plan_id' => $payment->pricing_plan_id],
            [
                'status' => $status,
                'started_at' => now(),
                'expires_at' => $expiresAt,
                'trial_ends_at' => $status === 'trial' ? $expiresAt : null,
                'amount_paid' => $payment->amount,
                'payment_method' => 'paystack',
                'transaction_id' => $payment->transaction_id,
                'metadata' => [
                    'payment_id' => $payment->id,
                    'reference' => $payment->reference,
                ],
            ]
        );
    }

    /**
     * Compare two amounts with tolerance for floating point precision
     * Allows for up to 0.01 difference (1 pesewa)
     */
    private function amountsMatch(float $expected, float $paid): bool
    {
        $tolerance = 0.01; // 1 pesewa tolerance
        return abs($expected - $paid) < $tolerance;
    }

    /**
     * Calculate subscription expiry date
     */
    private function calculateExpiryDate(string $duration): ?\Carbon\Carbon
    {
        $daysInMonth = now()->daysInMonth;

        return match ($duration) {
            'trial' => now()->addDays(7),
            'month' => now()->addDays($daysInMonth),
            '3month' => now()->addDays(3 * $daysInMonth),
            '6month' => now()->addDays(6 * $daysInMonth),
            '12month' => now()->addDays(12 * $daysInMonth),
            default => null,
        };
    }
}
