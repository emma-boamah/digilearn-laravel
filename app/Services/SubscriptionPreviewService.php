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
            return [
                'level' => 'preview',
                'reason' => 'subscription_required',
                'restricted_fields' => ['video_url', 'quiz_id', 'documents'],
                'required_plan' => $this->getRequiredPlanForLevel($lesson['level'])
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
        return [
            'title' => 'Premium Lesson - Upgrade Required',
            'message' => sprintf(
                "Access '%s' and thousands of other premium lessons with our %s plan.",
                $lesson['title'],
                $this->getRequiredPlanForLevel($lesson['level'])
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
            'required_plan' => $this->getRequiredPlanForLevel($lesson['level']),
            'current_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free'
        ];
    }
    
    private function getRequiredPlanForLevel(string $level): string
    {
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
}