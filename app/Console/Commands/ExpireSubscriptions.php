<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use App\Models\UserProgress;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscription:expire';

    protected $description = 'Mark expired subscriptions, manage grace periods, and clean up old progress';

    /**
     * Grace period duration in days.
     */
    private const GRACE_PERIOD_DAYS = 3;

    /**
     * Days after final expiry before progress is soft-deactivated.
     */
    private const PROGRESS_RETENTION_DAYS = 90;

    public function handle()
    {
        $this->info('Starting subscription expiry processing...');

        $movedToGrace = $this->moveExpiredToGracePeriod();
        $fullyExpired = $this->expireGracePeriodSubscriptions();
        $trialExpired = $this->expireTrials();
        $progressCleaned = $this->cleanupOldProgress();

        $this->info("Done. Grace period: {$movedToGrace}, Fully expired: {$fullyExpired}, Trials expired: {$trialExpired}, Progress cleaned: {$progressCleaned}");
    }

    /**
     * Move active subscriptions past expires_at into grace period.
     */
    private function moveExpiredToGracePeriod(): int
    {
        $subscriptions = UserSubscription::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($subscriptions as $subscription) {
            $subscription->update([
                'status' => 'grace_period',
                'grace_period_ends_at' => $subscription->expires_at->addDays(self::GRACE_PERIOD_DAYS),
            ]);

            Log::info('Subscription moved to grace period', [
                'user_id' => $subscription->user_id,
                'subscription_id' => $subscription->id,
                'expires_at' => $subscription->expires_at,
                'grace_period_ends_at' => $subscription->grace_period_ends_at,
            ]);
        }

        $count = $subscriptions->count();
        if ($count > 0) {
            $this->info("Moved {$count} subscriptions to grace period.");
        }

        return $count;
    }

    /**
     * Expire subscriptions whose grace period has ended.
     */
    private function expireGracePeriodSubscriptions(): int
    {
        $subscriptions = UserSubscription::where('status', 'grace_period')
            ->whereNotNull('grace_period_ends_at')
            ->where('grace_period_ends_at', '<=', now())
            ->get();

        foreach ($subscriptions as $subscription) {
            $subscription->update([
                'status' => 'expired',
            ]);

            Log::info('Subscription fully expired after grace period', [
                'user_id' => $subscription->user_id,
                'subscription_id' => $subscription->id,
            ]);
        }

        $count = $subscriptions->count();
        if ($count > 0) {
            $this->info("Fully expired {$count} subscriptions after grace period.");
        }

        return $count;
    }

    /**
     * Expire trials whose trial_ends_at has passed.
     */
    private function expireTrials(): int
    {
        $subscriptions = UserSubscription::where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->get();

        foreach ($subscriptions as $subscription) {
            $subscription->update([
                'status' => 'expired',
            ]);

            Log::info('Trial subscription expired', [
                'user_id' => $subscription->user_id,
                'subscription_id' => $subscription->id,
            ]);
        }

        $count = $subscriptions->count();
        if ($count > 0) {
            $this->info("Expired {$count} trial subscriptions.");
        }

        return $count;
    }

    /**
     * Soft-deactivate progress for subscriptions expired > 90 days ago.
     */
    private function cleanupOldProgress(): int
    {
        $cutoffDate = now()->subDays(self::PROGRESS_RETENTION_DAYS);

        // Find users whose subscriptions expired more than 90 days ago
        // and who don't have any current active subscription
        $expiredUserIds = UserSubscription::where('status', 'expired')
            ->where('expires_at', '<=', $cutoffDate)
            ->whereNotIn('user_id', function ($query) {
                $query->select('user_id')
                    ->from('user_subscriptions')
                    ->whereIn('status', ['active', 'trial', 'grace_period']);
            })
            ->pluck('user_id')
            ->unique();

        $count = 0;
        foreach ($expiredUserIds as $userId) {
            $updated = UserProgress::where('user_id', $userId)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'last_accessed_at' => now(),
                ]);
            $count += $updated;
        }

        if ($count > 0) {
            $this->info("Soft-deactivated {$count} progress records for users expired > " . self::PROGRESS_RETENTION_DAYS . " days.");
            Log::info("Progress cleanup: deactivated {$count} records for " . $expiredUserIds->count() . " users");
        }

        return $count;
    }
}
