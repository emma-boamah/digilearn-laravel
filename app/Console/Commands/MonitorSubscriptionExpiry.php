<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use App\Notifications\SubscriptionExpiringNotification;
use App\Notifications\SubscriptionExpiredNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class MonitorSubscriptionExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:monitor-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring subscriptions and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting subscription expiry check...');

        $this->checkExpiringSubscriptions(7);
        $this->checkExpiringSubscriptions(3);
        
        $this->checkExpiringTrials(3);
        $this->checkExpiringTrials(1);

        // Expiry-day and grace period notifications
        $this->checkExpiredToday();
        $this->checkGracePeriodEnding(1);

        $this->info('Subscription expiry check completed.');
    }

    /**
     * Check for subscriptions expiring in X days
     */
    private function checkExpiringSubscriptions(int $days)
    {
        $targetDate = Carbon::now()->addDays($days)->format('Y-m-d');

        $expiringSubscriptions = UserSubscription::where('status', 'active')
            ->whereDate('expires_at', $targetDate)
            ->with(['user', 'pricingPlan'])
            ->get();

        $count = $expiringSubscriptions->count();
        $this->info("Found {$count} active subscriptions expiring in {$days} days.");

        foreach ($expiringSubscriptions as $subscription) {
            if (!$subscription->user) {
                continue;
            }

            try {
                $subscription->user->notify(new SubscriptionExpiringNotification(
                    $days,
                    $subscription->pricingPlan->name ?? 'Plan'
                ));
                
                Log::info("Sent {$days}-day expiry notification", [
                    'user_id' => $subscription->user_id,
                    'subscription_id' => $subscription->id
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send {$days}-day expiry notification", [
                    'user_id' => $subscription->user_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Check for trials expiring in X days
     */
    private function checkExpiringTrials(int $days)
    {
        $targetDate = Carbon::now()->addDays($days)->format('Y-m-d');

        $expiringTrials = UserSubscription::where('status', 'trial')
            ->whereDate('trial_ends_at', $targetDate)
            ->with(['user', 'pricingPlan'])
            ->get();

        $count = $expiringTrials->count();
        $this->info("Found {$count} trials expiring in {$days} days.");

        foreach ($expiringTrials as $subscription) {
            if (!$subscription->user) {
                continue;
            }

            try {
                $planName = ($subscription->pricingPlan->name ?? 'Plan') . ' (Trial)';
                
                $subscription->user->notify(new SubscriptionExpiringNotification(
                    $days,
                    $planName
                ));
                
                Log::info("Sent {$days}-day trial expiry notification", [
                    'user_id' => $subscription->user_id,
                    'subscription_id' => $subscription->id
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send {$days}-day trial expiry notification", [
                    'user_id' => $subscription->user_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Notify users whose subscriptions expired today (now in grace period).
     */
    private function checkExpiredToday()
    {
        $today = Carbon::now()->format('Y-m-d');

        // Find subscriptions that just moved to grace period today
        $expiredToday = UserSubscription::where('status', 'grace_period')
            ->whereDate('expires_at', $today)
            ->with(['user', 'pricingPlan'])
            ->get();

        $count = $expiredToday->count();
        $this->info("Found {$count} subscriptions that expired today.");

        foreach ($expiredToday as $subscription) {
            if (!$subscription->user) {
                continue;
            }

            try {
                $subscription->user->notify(new SubscriptionExpiredNotification(
                    $subscription->pricingPlan->name ?? 'Plan',
                    'expired'
                ));

                Log::info('Sent subscription expired notification', [
                    'user_id' => $subscription->user_id,
                    'subscription_id' => $subscription->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send subscription expired notification', [
                    'user_id' => $subscription->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Notify users whose grace period ends in X days.
     */
    private function checkGracePeriodEnding(int $days)
    {
        $targetDate = Carbon::now()->addDays($days)->format('Y-m-d');

        $gracePeriodEnding = UserSubscription::where('status', 'grace_period')
            ->whereDate('grace_period_ends_at', $targetDate)
            ->with(['user', 'pricingPlan'])
            ->get();

        $count = $gracePeriodEnding->count();
        $this->info("Found {$count} grace periods ending in {$days} day(s).");

        foreach ($gracePeriodEnding as $subscription) {
            if (!$subscription->user) {
                continue;
            }

            try {
                $subscription->user->notify(new SubscriptionExpiredNotification(
                    $subscription->pricingPlan->name ?? 'Plan',
                    'grace_ending',
                    $days
                ));

                Log::info("Sent grace period ending notification ({$days} day(s))", [
                    'user_id' => $subscription->user_id,
                    'subscription_id' => $subscription->id,
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send grace period ending notification", [
                    'user_id' => $subscription->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
