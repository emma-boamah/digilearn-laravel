<?php

namespace App\Services;

use App\Models\User;
use App\Services\SubscriptionAccessService;
use Illuminate\Support\Str;

class SubscriptionPreviewService
{
    public function processRelatedLessons(array $lessons, User $user): array
    {
        foreach ($lessons as &$lesson) {
            $lesson['access_info'] = $this->determineAccessLevel($lesson, $user);
            
            if ($lesson['access_info']['level'] === 'preview') {
                $lesson['access_info']['upgrade_prompt'] = $this->generateUpgradePrompt($lesson, $user);
                
                // Strip restricted fields
                foreach ($lesson['access_info']['restricted_fields'] as $field) {
                    if (array_key_exists($field, $lesson)) {
                        $lesson[$field] = null;
                        if ($field === 'video_url') {
                            $lesson['video_url'] = ''; // Ensure it's an empty string for UI checks
                        }
                    }
                }
                
                // Also hide documentation count if restricted
                if (in_array('documents', $lesson['access_info']['restricted_fields'])) {
                    $lesson['documents_count'] = 0;
                }
            }
        }
        
        return $lessons;
    }
    
    private function determineAccessLevel(array $lesson, User $user): array
    {
        // Superuser bypass
        if ($user->is_superuser) {
            return [
                'level' => 'full',
                'reason' => 'superuser',
                'restricted_fields' => []
            ];
        }
        
        // Check subscription access
        $hasAccess = SubscriptionAccessService::canAccessGradeLevel($user, $lesson['grade_level'] ?? $lesson['level']);
        
        if (!$hasAccess) {
            $planName = $this->getRequiredPlanForLevel($lesson['level'] ?? $lesson['grade_level']);
            $planSlug = $this->getRequiredPlanSlugForLevel($lesson['level'] ?? $lesson['grade_level']);
            
            return [
                'level' => 'preview',
                'reason' => 'subscription_required',
                'restricted_fields' => ['video_url', 'quiz_id', 'documents'],
                'required_plan' => $planName,
                'required_plan_slug' => $planSlug
            ];
        }
        
        return [
            'level' => 'full',
            'reason' => 'subscription_granted',
            'restricted_fields' => []
        ];
    }
    
    private function generateUpgradePrompt(array $lesson, User $user): array
    {
        $planName = $this->getRequiredPlanForLevel($lesson['level'] ?? $lesson['grade_level']);
        $planSlug = $this->getRequiredPlanSlugForLevel($lesson['level'] ?? $lesson['grade_level']);

        return [
            'title' => 'Premium Lesson - Upgrade Required',
            'message' => sprintf(
                "Access '%s' and thousands of other premium lessons with our %s plan.",
                $lesson['title'],
                $planName
            ),
            'features' => [
                'Full video access',
                'Downloadable resources',
                'Interactive quizzes', 
                'Progress tracking',
                'Certificates of completion'
            ],
            'cta_text' => 'Upgrade to Access',
            'cta_url' => route('pricing'),
            'required_plan' => $planName,
            'required_plan_slug' => $planSlug,
            'current_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free'
        ];
    }
    
    private function getRequiredPlanForLevel(?string $level): string
    {
        if (!$level) return 'Essential';

        // Normalize level string for comparison
        $levelSlug = Str::slug($level);

        // University levels require Essential Pro
        if (Str::contains($levelSlug, ['university', 'uni-', 'tertiary'])) {
            return 'Essential Pro';
        }

        // SHS levels require Essential Plus
        if (Str::contains($levelSlug, ['shs', 'senior-high'])) {
            return 'Essential Plus';
        }

        // Primary and JHS levels require Essential (Default)
        return 'Essential';
    }

    private function getRequiredPlanSlugForLevel(?string $level): string
    {
        if (!$level) return 'essential';

        $levelSlug = Str::slug($level);

        if (Str::contains($levelSlug, ['university', 'uni-', 'tertiary'])) {
            return 'essential-pro';
        }

        if (Str::contains($levelSlug, ['shs', 'senior-high'])) {
            return 'essential-plus';
        }

        return 'essential';
    }
}