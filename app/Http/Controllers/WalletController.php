<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    protected PaystackService $paystack;

    public function __construct(PaystackService $paystack)
    {
        $this->paystack = $paystack;
    }

    /**
     * Show the wallet top-up page.
     */
    public function index()
    {
        $user = Auth::user();
        $creditBalance = (float) $user->credit_balance;

        // Recent wallet top-up history
        $recentTopups = Payment::where('user_id', $user->id)
            ->whereJsonContains('metadata->type', 'credit_topup')
            ->where('status', 'success')
            ->orderBy('paid_at', 'desc')
            ->take(5)
            ->get();

        return view('wallet.index', compact('user', 'creditBalance', 'recentTopups'));
    }

    /**
     * Initiate a wallet top-up payment via Paystack.
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10|max:5000',
        ]);

        $user = Auth::user();

        // Check if user is suspended
        if ($user->isSuspended()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is suspended.',
            ], 403);
        }

        $amount = (float) $request->amount;
        $reference = 'WALLET-' . Str::uuid();

        // Create payment record
        $payment = Payment::create([
            'user_id'         => $user->id,
            'pricing_plan_id' => null,
            'amount'          => $amount,
            'currency'        => 'GHS',
            'reference'       => $reference,
            'status'          => 'pending',
            'metadata'        => [
                'type'        => 'credit_topup',
                'description' => 'Credit wallet top-up',
            ],
        ]);

        try {
            $paystackResponse = $this->paystack->initializePayment([
                'email'        => $user->email,
                'amount'       => $amount * 100, // Convert to pesewas
                'reference'    => $reference,
                'callback_url' => route('payment.callback'),
                'metadata'     => [
                    'payment_id' => $payment->id,
                    'user_id'    => $user->id,
                    'type'       => 'credit_topup',
                ],
            ]);

            return response()->json([
                'success'           => true,
                'authorization_url' => $paystackResponse['data']['authorization_url'],
                'reference'         => $reference,
            ]);
        } catch (\Exception $e) {
            Log::error('Wallet top-up Paystack initialization failed', [
                'error'      => $e->getMessage(),
                'payment_id' => $payment->id,
            ]);

            $payment->update(['status' => 'failed']);

            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed. Please try again.',
            ], 500);
        }
    }
}
