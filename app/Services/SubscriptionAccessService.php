<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSubscription;
use App\Models\PricingPlan;

class SubscriptionAccessService
{
    /**
     * Define which grade levels each plan has access to
     */
    private static $planAccessLevels = [
        'essential' => [
            'primary-lower', // Primary 1-3 (grade 1-3)
            'primary-upper', // Primary 4-6 (grade 4-6)
            'jhs'           // JHS 1-3
        ],
        'home_school' => [
            'primary-lower', // Primary 1-3
            'primary-upper', // Primary 4-6
            'jhs',          // JHS 1-3
            'shs'           // SHS 1-3
        ],
        'extra_tuition' => [
            'primary-lower', // Primary 1-3
            'primary-upper', // Primary 4-6
            'jhs',          // JHS 1-3
            'shs',          // SHS 1-3
            'university'    // University
        ]
    ];

    /**
     * Map level groups to grade levels
     */
    private static $levelGroupToGrades = [
        'primary-lower' => ['Primary 1', 'Primary 2', 'Primary 3'],
        'primary-upper' => ['Primary 4', 'Primary 5', 'Primary 6'],
        'jhs' => ['JHS 1', 'JHS 2', 'JHS 3'],
        'shs' => ['SHS 1', 'SHS 2', 'SHS 3'],
        'university' => ['University']
    ];

    /**
     * Check if user has access to a specific level group
     */
    public static function canAccessLevelGroup(User $user, string $levelGroup): bool
    {
        $userPlan = self::getUserPlanSlug($user);

        if (!$userPlan) {
            return false; // No active subscription
        }

        $allowedGroups = self::$planAccessLevels[$userPlan] ?? [];

        return in_array($levelGroup, $allowedGroups);
    }

    /**
     * Check if user has access to a specific grade level
     */
    public static function canAccessGradeLevel(User $user, string $gradeLevel): bool
    {
        $userPlan = self::getUserPlanSlug($user);

        if (!$userPlan) {
            return false;
        }

        $allowedGroups = self::$planAccessLevels[$userPlan] ?? [];
        $allowedGrades = [];

        foreach ($allowedGroups as $group) {
            $allowedGrades = array_merge($allowedGrades, self::$levelGroupToGrades[$group] ?? []);
        }

        return in_array($gradeLevel, $allowedGrades);
    }

    /**
     * Get all allowed grade levels for user
     */
    public static function getAllowedGradeLevels(User $user): array
    {
        $userPlan = self::getUserPlanSlug($user);

        if (!$userPlan) {
            return [];
        }

        $allowedGroups = self::$planAccessLevels[$userPlan] ?? [];
        $allowedGrades = [];

        foreach ($allowedGroups as $group) {
            $allowedGrades = array_merge($allowedGrades, self::$levelGroupToGrades[$group] ?? []);
        }

        return array_unique($allowedGrades);
    }

    /**
     * Get all allowed level groups for user
     */
    public static function getAllowedLevelGroups(User $user): array
    {
        $userPlan = self::getUserPlanSlug($user);

        if (!$userPlan) {
            return [];
        }

        return self::$planAccessLevels[$userPlan] ?? [];
    }

    /**
     * Get user's current active plan slug
     */
    private static function getUserPlanSlug(User $user): ?string
    {
        $currentSubscription = $user->currentSubscription;

        if (!$currentSubscription) {
            return null;
        }

        $planSlug = $currentSubscription->pricingPlan->slug ?? null;

        // Map plan slugs to our access levels
        $slugMapping = [
            'essential' => 'essential',
            'home-school' => 'home_school',
            'extra-tuition' => 'extra_tuition',
            // Add more mappings as needed
        ];

        return $slugMapping[$planSlug] ?? null;
    }

    /**
     * Filter content query based on user's subscription
     */
    public static function filterContentBySubscription($query, User $user, string $gradeLevelColumn = 'grade_level')
    {
        $allowedGrades = self::getAllowedGradeLevels($user);

        if (empty($allowedGrades)) {
            // No access to any content
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($gradeLevelColumn, $allowedGrades);
    }
}