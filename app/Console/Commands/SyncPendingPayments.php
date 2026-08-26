<?php

namespace App\Console\Commands;

use App\Http\Controllers\PaymentController;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncPendingPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:sync-pending 
                            {--days=30 : Number of days back to check pending payments}
                            {--reference= : Specific payment reference to verify}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify and sync pending payments against Paystack API to activate subscriptions';

    /**
     * Execute the console command.
     */
    public function handle(PaymentController $paymentController)
    {
        $specificReference = $this->option('reference');
        $days = (int) $this->option('days');

        if ($specificReference) {
            $query = Payment::where('reference', $specificReference);
        } else {
            $query = Payment::where('status', 'pending')
                ->where('created_at', '>=', now()->subDays($days));
        }

        $payments = $query->get();

        if ($payments->isEmpty()) {
            $this->info('No pending payments found to verify.');
            return 0;
        }

        $this->info("Found {$payments->count()} payment(s) to verify against Paystack...");

        $successCount = 0;
        $failedCount = 0;
        $stillPendingCount = 0;

        foreach ($payments as $payment) {
            $this->line("Checking Payment #{$payment->id} (Ref: {$payment->reference}, User: {$payment->user_id}, Amount: {$payment->currency} {$payment->amount})...");

            $result = $paymentController->verifyAndSyncPayment($payment);

            if ($result['success'] && ($result['status'] ?? '') === 'success') {
                $this->info(" -> [SUCCESS] Payment verified and subscription activated!");
                $successCount++;
            } elseif (($result['status'] ?? '') === 'failed' || ($result['status'] ?? '') === 'abandoned') {
                $this->warn(" -> [FAILED/ABANDONED] Payment status on Paystack: {$result['status']}");
                $failedCount++;
            } else {
                $this->line(" -> [PENDING/OTHER] {$result['message']}");
                $stillPendingCount++;
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->table(
            ['Total Checked', 'Activated (Success)', 'Failed/Abandoned', 'Still Pending'],
            [[$payments->count(), $successCount, $failedCount, $stillPendingCount]]
        );

        Log::info('Console command payments:sync-pending executed', [
            'total_checked' => $payments->count(),
            'activated' => $successCount,
            'failed' => $failedCount,
            'still_pending' => $stillPendingCount,
        ]);

        return 0;
    }
}
