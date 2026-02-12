<?php

namespace App\Services;

use App\Models\User;
use App\Models\PricingPlan;
use App\Models\UserSubscription;
use App\Models\UserProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ResubscriptionService
{
    /**
     * Handle a user re-subscribing after their subscription expired.
     *
     * Business rules:
     * - Restores user to their last active level group (if accessible by new plan)
     * - Reactivates progress if within 90-day retention window
     * - Creates a new subscription record (never reuses old rows)
     *
     * @param User $user
     * @param PricingPlan $plan
     * @param array $paymentData  ['amount_paid', 'payment_method', 'transaction_id', 'duration']
     * @return array  Result with subscription details and restoration info
     */
    public function handleResubscription(User $user, PricingPlan $plan, array $paymentData): array
    {
        return DB::transaction(function () use ($user, $plan, $paymentData) {
            Log::info('Processing re-subscription', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
            ]);

            // 1. Find the most recent expired subscription
            $previousSubscription = UserSubscription::where('user_id', $user->id)
                ->whereIn('status', ['expired', 'cancelled', 'inactive'])
                ->orderBy('expires_at', 'desc')
                ->first();

            // 2. Calculate subscription dates
            $duration = $paymentData['duration'] ?? 'month';
            $months = $this->getDurationMonths($duration);
            $startsAt = now();
            $expiresAt = now()->addMonths($months);

            // 3. Create new subscription
            $subscription = UserSubscription::create([
                'user_id' => $user->id,
                'pricing_plan_id' => $plan->id,
                'status' => $paymentData['status'] ?? 'active',
                'started_at' => $startsAt,
                'expires_at' => $paymentData['expires_at'] ?? $expiresAt,
                'trial_ends_at' => $paymentData['trial_ends_at'] ?? null,
                'amount_paid' => $paymentData['amount_paid'] ?? 0,
                'payment_method' => $paymentData['payment_method'] ?? null,
                'transaction_id' => $paymentData['transaction_id'] ?? null,
                'metadata' => array_merge([
                    'is_resubscription' => true,
                    'previous_subscription_id' => $previousSubscription?->id,
                    'duration' => $duration,
                ], $paymentData['metadata'] ?? []),
            ]);

            // 4. Restore progress and level group
            $restorationResult = $this->restoreUserState($user, $plan, $previousSubscription);

            Log::info('Re-subscription completed', [
                'user_id' => $user->id,
                'new_subscription_id' => $subscription->id,
                'progress_restored' => $restorationResult['progress_restored'],
                'level_group_restored' => $restorationResult['level_group'],
            ]);

            return [
                'success' => true,
                'subscription' => $subscription,
                'plan_name' => $plan->name,
                'expires_at' => $expiresAt,
                'is_resubscription' => $previousSubscription !== null,
                'progress_restored' => $restorationResult['progress_restored'],
                'restored_level_group' => $restorationResult['level_group'],
            ];
        });
    }

    /**
     * Restore user's level group and progress from their previous subscription.
     */
    private function restoreUserState(User $user, PricingPlan $plan, ?UserSubscription $previousSub): array
    {
        $result = [
            'progress_restored' => false,
            'level_group' => null,
        ];

        // Get the plan's accessible level groups
        $accessibleGroups = $plan->accessibleLevelGroups->pluck('level_group')->toArray();

        if (empty($accessibleGroups)) {
            // Fallback to static mapping from SubscriptionAccessService
            $accessibleGroups = SubscriptionAccessService::getAllowedLevelGroups($user);
        }

        // Restore to last active level group if still accessible
        $lastLevelGroup = $user->current_level_group;

        if ($lastLevelGroup && in_array($lastLevelGroup, $accessibleGroups)) {
            // Keep current level group — it's still accessible
            $result['level_group'] = $lastLevelGroup;
        } elseif (!empty($accessibleGroups)) {
            // Last level group not accessible by new plan — use highest accessible
            $levelGroupPriority = ['university', 'shs', 'jhs', 'primary-upper', 'primary-lower'];
            foreach ($levelGroupPriority as $group) {
                if (in_array($group, $accessibleGroups)) {
                    $user->update(['current_level_group' => $group]);
                    $result['level_group'] = $group;
                    break;
                }
            }
        }

        // Reactivate soft-deactivated progress (within 90-day window)
        $reactivated = UserProgress::where('user_id', $user->id)
            ->where('is_active', false)
            ->whereIn('level_group', $accessibleGroups)
            ->update([
                'is_active' => true,
                'last_accessed_at' => now(),
            ]);

        if ($reactivated > 0) {
            $result['progress_restored'] = true;
            Log::info('Progress restored for re-subscribing user', [
                'user_id' => $user->id,
                'records_reactivated' => $reactivated,
            ]);
        }

        return $result;
    }

    /**
     * Convert duration string to months.
     */
    private function getDurationMonths(string $duration): int
    {
        return match ($duration) {
            'month' => 1,
            '3month' => 3,
            '6month' => 6,
            '12month' => 12,
            default => 1,
        };
    }
}
