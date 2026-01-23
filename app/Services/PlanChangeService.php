<?php

namespace App\Services;

use App\Models\User;
use App\Models\PricingPlan;
use App\Models\UserProgress;
use App\Models\LevelProgression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Notifications\PlanChangeNotification;

class PlanChangeService
{
    /**
     * Handle plan change for a user with specific business logic
     *
     * Business Rules:
     * - Downgrades: Immediate access blocking, no progress preservation, no grace period
     * - Upgrades: Immediate charges, subscription extension, progress preservation
     */
    public function changeUserPlan(User $user, int $newPlanId): array
    {
        return DB::transaction(function () use ($user, $newPlanId) {
            $currentSubscription = $user->currentSubscription;
            if (!$currentSubscription) {
                throw new \Exception('User has no active subscription');
            }

            $oldPlan = $currentSubscription->pricingPlan;
            $newPlan = PricingPlan::findOrFail($newPlanId);

            // Analyze access changes
            $accessAnalysis = $this->analyzeAccessChanges($oldPlan, $newPlan);

            // Handle the plan change based on upgrade/downgrade
            if ($this->isUpgrade($oldPlan, $newPlan)) {
                return $this->handleUpgrade($user, $currentSubscription, $oldPlan, $newPlan, $accessAnalysis);
            } else {
                return $this->handleDowngrade($user, $currentSubscription, $oldPlan, $newPlan, $accessAnalysis);
            }
        });
    }

    /**
     * Determine if this is an upgrade based on plan hierarchy
     */
    private function isUpgrade(PricingPlan $oldPlan, PricingPlan $newPlan): bool
    {
        $planHierarchy = [
            'essential' => 1,
            'essential-plus' => 2,
            'essential-pro' => 3
        ];

        $oldLevel = $planHierarchy[$oldPlan->slug] ?? 0;
        $newLevel = $planHierarchy[$newPlan->slug] ?? 0;

        return $newLevel > $oldLevel;
    }

    /**
     * Handle plan upgrade
     */
    private function handleUpgrade(User $user, $subscription, PricingPlan $oldPlan, PricingPlan $newPlan, array $accessAnalysis): array
    {
        Log::info('Processing plan upgrade', [
            'user_id' => $user->id,
            'from_plan' => $oldPlan->name,
            'to_plan' => $newPlan->name
        ]);

        // Calculate prorated charge for upgrade
        $proratedCharge = $this->calculateProratedUpgradeCharge($subscription, $oldPlan, $newPlan);

        // Update subscription
        $subscription->update([
            'pricing_plan_id' => $newPlan->id
        ]);

        // Progress is preserved for upgrades (no changes needed)

        // Send notification
        $this->sendPlanChangeNotification($user, $oldPlan->name, $newPlan->name, 'upgrade', $proratedCharge, $accessAnalysis);

        return [
            'success' => true,
            'type' => 'upgrade',
            'old_plan' => $oldPlan->name,
            'new_plan' => $newPlan->name,
            'prorated_charge' => $proratedCharge,
            'access_changes' => $accessAnalysis,
            'progress_preserved' => true
        ];
    }

    /**
     * Handle plan downgrade
     */
    private function handleDowngrade(User $user, $subscription, PricingPlan $oldPlan, PricingPlan $newPlan, array $accessAnalysis): array
    {
        Log::info('Processing plan downgrade', [
            'user_id' => $user->id,
            'from_plan' => $oldPlan->name,
            'to_plan' => $newPlan->name,
            'current_level_group' => $user->current_level_group
        ]);

        // Check if user currently has access to a level group that new plan doesn't support
        $currentLevelGroup = $user->current_level_group;

        if ($currentLevelGroup && !$newPlan->canAccessLevelGroup($currentLevelGroup)) {
            // Immediate access blocking - redirect to highest available level group
            $this->handleImmediateAccessBlocking($user, $newPlan);

            // Reset user progress for restricted content
            $this->resetProgressForRestrictedContent($user, $newPlan);
        }

        // Update subscription
        $subscription->update([
            'pricing_plan_id' => $newPlan->id
        ]);

        // Calculate any refund (if applicable)
        $refundAmount = $this->calculateDowngradeRefund($subscription, $oldPlan, $newPlan);

        // Send notification
        $this->sendPlanChangeNotification($user, $oldPlan->name, $newPlan->name, 'downgrade', null, $accessAnalysis);

        return [
            'success' => true,
            'type' => 'downgrade',
            'old_plan' => $oldPlan->name,
            'new_plan' => $newPlan->name,
            'refund_amount' => $refundAmount,
            'access_blocked' => $accessAnalysis['requires_downgrade'],
            'new_accessible_groups' => $accessAnalysis['new_accessible_groups'],
            'progress_preserved' => false
        ];
    }

    /**
     * Analyze what access changes will occur
     */
    private function analyzeAccessChanges(PricingPlan $oldPlan, PricingPlan $newPlan): array
    {
        $oldGroups = $oldPlan->accessibleLevelGroups->pluck('level_group')->toArray();
        $newGroups = $newPlan->accessibleLevelGroups->pluck('level_group')->toArray();

        $lostAccess = array_diff($oldGroups, $newGroups);
        $gainedAccess = array_diff($newGroups, $oldGroups);

        return [
            'lost_access' => $lostAccess,
            'gained_access' => $gainedAccess,
            'requires_downgrade' => !empty($lostAccess),
            'old_accessible_groups' => $oldGroups,
            'new_accessible_groups' => $newGroups
        ];
    }

    /**
     * Handle immediate access blocking for downgrades
     */
    private function handleImmediateAccessBlocking(User $user, PricingPlan $newPlan): void
    {
        $accessibleGroups = $newPlan->accessibleLevelGroups->pluck('level_group')->toArray();

        // If user has no accessible groups, this is an error
        if (empty($accessibleGroups)) {
            Log::error('Plan has no accessible level groups', [
                'plan_id' => $newPlan->id,
                'plan_name' => $newPlan->name
            ]);
            return;
        }

        // Redirect to the "lowest" accessible level group
        $levelGroupPriority = ['primary-lower', 'primary-upper', 'jhs', 'shs', 'university'];
        $newLevelGroup = null;

        foreach ($levelGroupPriority as $group) {
            if (in_array($group, $accessibleGroups)) {
                $newLevelGroup = $group;
                break;
            }
        }

        if ($newLevelGroup) {
            // Update user's current level group
            $user->update(['current_level_group' => $newLevelGroup]);

            // Reset progress for the new level group
            $this->initializeProgressForLevelGroup($user, $newLevelGroup);

            Log::info('User access blocked and redirected', [
                'user_id' => $user->id,
                'old_level_group' => $user->current_level_group,
                'new_level_group' => $newLevelGroup,
                'reason' => 'plan_downgrade'
            ]);
        }
    }

    /**
     * Reset progress for content that user can no longer access
     */
    private function resetProgressForRestrictedContent(User $user, PricingPlan $newPlan): void
    {
        $accessibleGroups = $newPlan->accessibleLevelGroups->pluck('level_group')->toArray();

        // Reset progress for level groups user can no longer access
        UserProgress::where('user_id', $user->id)
            ->whereNotIn('level_group', $accessibleGroups)
            ->update([
                'is_active' => false,
                'last_accessed_at' => now()
            ]);

        Log::info('User progress reset for restricted content', [
            'user_id' => $user->id,
            'accessible_groups' => $accessibleGroups
        ]);
    }

    /**
     * Initialize progress for a new level group
     */
    private function initializeProgressForLevelGroup(User $user, string $levelGroup): void
    {
        // Similar to the logic in DashboardController
        $lowestGrade = $this->getLowestGradeForLevelGroup($levelGroup);

        UserProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'level_group' => $levelGroup,
                'current_level' => $lowestGrade
            ],
            [
                'is_active' => true,
                'last_accessed_at' => now(),
                'level_started_at' => now(),
                'total_lessons_in_level' => 0, // Will be calculated elsewhere
                'completed_lessons' => 0,
                'total_quizzes_in_level' => 0,
                'completed_quizzes' => 0,
                'average_quiz_score' => 0,
                'completion_percentage' => 0
            ]
        );
    }

    /**
     * Calculate prorated charge for upgrade
     */
    private function calculateProratedUpgradeCharge($subscription, PricingPlan $oldPlan, PricingPlan $newPlan): float
    {
        // Calculate remaining days in subscription
        $expiresAt = Carbon::parse($subscription->expires_at);
        $now = Carbon::now();
        $remainingDays = $now->diffInDays($expiresAt, false);

        if ($remainingDays <= 0) {
            return $newPlan->price; // Full price if expired
        }

        // Calculate daily rate difference
        $oldDailyRate = $oldPlan->price / 30; // Assuming monthly billing
        $newDailyRate = $newPlan->price / 30;

        $rateDifference = $newDailyRate - $oldDailyRate;

        return max(0, $rateDifference * $remainingDays);
    }

    /**
     * Calculate refund for downgrade
     */
    private function calculateDowngradeRefund($subscription, PricingPlan $oldPlan, PricingPlan $newPlan): float
    {
        // For simplicity, no refunds on downgrades
        // In a real implementation, this might calculate prorated refunds
        return 0.0;
    }

    /**
     * Get the lowest grade for a level group
     */
    private function getLowestGradeForLevelGroup(string $levelGroup): string
    {
        $lowestGrades = [
            'primary-lower' => 'Primary 1',
            'primary-upper' => 'Primary 4',
            'jhs' => 'JHS 1',
            'shs' => 'SHS 1',
            'university' => 'University Year 1',
        ];

        return $lowestGrades[$levelGroup] ?? $levelGroup;
    }

    /**
     * Send plan change notification to user
     */
    private function sendPlanChangeNotification(User $user, string $oldPlan, string $newPlan, string $changeType, ?float $proratedCharge, array $accessChanges): void
    {
        try {
            $notification = new PlanChangeNotification($oldPlan, $newPlan, $changeType, $proratedCharge, $accessChanges);
            $user->notify($notification);

            Log::info('Plan change notification sent', [
                'user_id' => $user->id,
                'old_plan' => $oldPlan,
                'new_plan' => $newPlan,
                'change_type' => $changeType,
                'prorated_charge' => $proratedCharge
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send plan change notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'old_plan' => $oldPlan,
                'new_plan' => $newPlan
            ]);
        }
    }
}