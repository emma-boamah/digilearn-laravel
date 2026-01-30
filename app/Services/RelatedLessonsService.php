<?php

namespace App\Services;

use App\Models\User;
use App\Models\Video;
use App\Models\UserEngagement;
use App\Services\SubscriptionAccessService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RelatedLessonsService
{
    private $lessonDifficultyService;
    private $subscriptionPreviewService;
    
    public function __construct(
        LessonDifficultyService $lessonDifficultyService,
        SubscriptionPreviewService $subscriptionPreviewService
    ) {
        $this->lessonDifficultyService = $lessonDifficultyService;
        $this->subscriptionPreviewService = $subscriptionPreviewService;
    }
    
    public function getRelatedLessons(array $currentLesson, User $user, array $filters = []): array
    {
        // Standardize current lesson
        $currentLesson['level'] = $currentLesson['level'] ?? $currentLesson['grade_level'] ?? 'Primary 1';
        $currentLesson['id'] = $currentLesson['id'] ?? $currentLesson['video_id'] ?? 0;
        
        // Handle subject name extraction
        if (isset($currentLesson['subject']) && is_array($currentLesson['subject'])) {
            $currentLesson['subject'] = $currentLesson['subject']['name'] ?? 'General';
        } else {
            $currentLesson['subject'] = $currentLesson['subject'] ?? 'General';
        }
        
        // Handle instructor name extraction
        if (isset($currentLesson['uploader']) && is_array($currentLesson['uploader'])) {
            $currentLesson['instructor'] = $currentLesson['uploader']['name'] ?? 'Unknown';
        } else {
            $currentLesson['instructor'] = $currentLesson['instructor'] ?? 'Unknown';
        }
        
        $cacheKey = $this->generateCacheKey($currentLesson['id'], $user->id, $filters);
        
        return Cache::remember($cacheKey, $this->getCacheTTL(), function () use ($currentLesson, $user, $filters) {
            Log::info('Computing related lessons cache miss', [
                'lesson_id' => $currentLesson['id'],
                'user_id' => $user->id,
                'filters' => $filters
            ]);
            
            return $this->computeRelatedLessons($currentLesson, $user, $filters);
        });
    }
    
    private function computeRelatedLessons(array $currentLesson, User $user, array $filters): array
    {
        // 1. Get candidate lessons from appropriate source
        $candidates = $this->getCandidateLessons($currentLesson, $user, $filters);
        
        // 2. Score each candidate using multi-factor algorithm
        $scoredLessons = [];
        foreach ($candidates as $candidate) {
            if ($candidate['id'] === $currentLesson['id']) continue;
            
            $score = $this->calculateRelatedLessonScore($currentLesson, $candidate, $user);
            $candidate['related_score'] = $score;
            $candidate['scoring_breakdown'] = $this->getScoringBreakdown($currentLesson, $candidate, $user);
            $scoredLessons[] = $candidate;
        }
        
        // 3. Sort by score and apply filters
        usort($scoredLessons, fn($a, $b) => $b['related_score'] <=> $a['related_score']);
        
        $limit = $filters['limit'] ?? 12;
        $limitedLessons = array_slice($scoredLessons, 0, $limit);
        
        // 4. Apply subscription preview logic
        return $this->subscriptionPreviewService->processRelatedLessons($limitedLessons, $user);
    }
    
    private function calculateRelatedLessonScore(array $currentLesson, array $candidate, User $user): float
    {
        $score = 0;
        
        // Subject Similarity (40% weight)
        $subjectScore = $this->calculateSubjectSimilarity($currentLesson['subject'], $candidate['subject']);
        $score += $subjectScore * 0.40;
        
        // Difficulty Progression (25% weight) - WITH COMPLETE PRIMARY PROGRESSION
        $difficultyScore = $this->lessonDifficultyService->getProgressionScore(
            $currentLesson['level'], 
            $candidate['level']
        );
        $score += $difficultyScore * 0.25;
        
        // Instructor Consistency (15% weight)
        $instructorScore = $this->calculateInstructorConsistency($currentLesson, $candidate);
        $score += $instructorScore * 0.15;
        
        // Engagement Metrics (15% weight)
        $engagementScore = $this->calculateEngagementScore($candidate, $user);
        $score += $engagementScore * 0.15;
        
        // User Learning Patterns (5% weight)
        $patternScore = $this->calculateLearningPatternScore($currentLesson, $candidate, $user);
        $score += $patternScore * 0.05;
        
        return round($score, 3);
    }
    
    private function calculateSubjectSimilarity(?string $currentSubject, ?string $candidateSubject): float
    {
        if (!$currentSubject || !$candidateSubject) return 0.5;
        
        // Exact match
        if (strcasecmp($currentSubject, $candidateSubject) === 0) {
            return 1.0;
        }
        
        // Related subject mapping
        $relatedSubjects = [
            'Mathematics' => ['Algebra', 'Geometry', 'Statistics', 'Calculus'],
            'Science' => ['Biology', 'Chemistry', 'Physics', 'General Science'],
            'English' => ['Literature', 'Grammar', 'Writing', 'Reading'],
            'Social Studies' => ['History', 'Geography', 'Civics', 'Economics'],
        ];
        
        foreach ($relatedSubjects as $main => $related) {
            if (in_array($currentSubject, $related) && in_array($candidateSubject, $related)) {
                return 0.8; // Same subject family
            }
        }
        
        return 0.3; // Different subject areas
    }
    
    private function calculateInstructorConsistency(array $currentLesson, array $candidate): float
    {
        $currentInstructor = $currentLesson['instructor'] ?? '';
        $candidateInstructor = $candidate['instructor'] ?? '';
        
        if (!$currentInstructor || !$candidateInstructor) return 0.5;
        
        // Exact match
        if (strcasecmp($currentInstructor, $candidateInstructor) === 0) {
            return 1.0;
        }
        
        // Check if same department/subject area
        $currentSubject = $currentLesson['subject'] ?? '';
        $candidateSubject = $candidate['subject'] ?? '';
        
        if ($currentSubject && $candidateSubject && strcasecmp($currentSubject, $candidateSubject) === 0) {
            return 0.6; // Same subject area, different instructor
        }
        
        return 0.2; // Different subject area
    }
    
    private function calculateEngagementScore(array $candidate, User $user): float
    {
        // Use existing engagement data from RecommendationService patterns
        try {
            $engagementStats = UserEngagement::where('content_id', $candidate['id'])
                ->where('content_type', 'video')
                ->avg('engagement_score');
                
            $views = $candidate['views'] ?? 0;
            
            // Normalize views to score (0-1 scale)
            $viewsScore = min(1.0, $views / 1000); // Cap at 1000 views
            
            // Combine engagement average and views
            $finalScore = ($engagementStats * 0.7) + ($viewsScore * 0.3);
            
            return min(1.0, $finalScore);
        } catch (\Exception $e) {
            // Fallback to views-based scoring if engagement data fails
            $views = $candidate['views'] ?? 0;
            return min(1.0, $views / 1000);
        }
    }
    
    private function calculateLearningPatternScore(array $currentLesson, array $candidate, User $user): float
    {
        // Simple pattern based on user's completed lessons
        // This can be enhanced with RecommendationService integration
        return 0.5; // Placeholder for Phase 1
    }
    
    private function getCandidateLessons(array $currentLesson, User $user, array $filters = []): array
    {
        // Get lessons from same level group
        $levelGroup = $this->getLevelGroupForLevel($currentLesson['level']);
        
        if ($levelGroup === 'university') {
            // University uses Course-based logic
            return $this->getUniversityLessons($currentLesson, $user);
        }
        
        // Regular level groups use Video model
        $gradeLevels = $this->getGradeLevelsForGroup($levelGroup);
        
        // Include next level group for progression (e.g., Primary 3 -> Primary 4)
        $nextGroup = $this->getNextLevelGroup($levelGroup);
        if ($nextGroup) {
            $gradeLevels = array_merge($gradeLevels, $this->getGradeLevelsForGroup($nextGroup));
        }
        
        $query = Video::approved()
            ->whereIn('grade_level', $gradeLevels)
            ->with(['uploader', 'subject'])
            ->where('id', '!=', $currentLesson['id']);
            
        // Apply subject filter if specified
        if (!empty($filters['subject']) && $filters['subject'] !== 'all') {
            $query->whereHas('subject', function($q) use ($filters) {
                $q->where('slug', $filters['subject']);
            });
        }
        
        $videos = $query->orderBy('created_at', 'desc')
            ->get();
            
        // Format to lesson structure
        $lessons = [];
        foreach ($videos as $video) {
            $lessons[] = [
                'id' => $video->id,
                'video_id' => $video->id,
                'title' => $video->title,
                'description' => $video->description,
                'duration' => $this->formatDuration($video->duration_seconds),
                'video_url' => $video->getVideoUrl(),
                'thumbnail' => $video->getThumbnailUrl(),
                'instructor' => $video->uploader ? $video->uploader->name : 'Unknown',
                'subject' => $video->subject ? $video->subject->name : 'General',
                'subject_id' => $video->subject_id,
                'subject_slug' => $video->subject ? strtolower(str_replace(' ', '-', $video->subject->name)) : 'general',
                'year' => $video->created_at->format('Y'),
                'level' => $video->grade_level,
                'level_display' => $this->getLevelDisplayName($video->grade_level),
                'views' => $video->views ?? 0,
                'grade_level' => $video->grade_level, // For subscription checks
                'video_source' => $video->video_source ?? 'local',
                'vimeo_id' => $video->vimeo_id,
                'external_video_id' => $video->external_video_id,
                'mux_playback_id' => $video->mux_playback_id,
                'documents_count' => $video->documents->count(),
                'has_quiz' => !empty($video->quiz),
                'quiz_id' => $video->quiz ? $video->quiz->id : null,
            ];
        }
        
        return $lessons;
    }
    
    private function getLevelGroupForLevel(string $level): string
    {
        $level = strtolower(str_replace(' ', '-', $level));
        
        $levelGroups = [
            'primary-1' => 'primary-lower', 'primary-2' => 'primary-lower', 'primary-3' => 'primary-lower',
            'primary-4' => 'primary-upper', 'primary-5' => 'primary-upper', 'primary-6' => 'primary-upper',
            'jhs-1' => 'jhs', 'jhs-2' => 'jhs', 'jhs-3' => 'jhs',
            'shs-1' => 'shs', 'shs-2' => 'shs', 'shs-3' => 'shs',
            'university' => 'university'
        ];
        
        return $levelGroups[$level] ?? 'primary-lower';
    }

    private function getNextLevelGroup(string $currentGroup): ?string
    {
        $progression = [
            'primary-lower' => 'primary-upper',
            'primary-upper' => 'jhs',
            'jhs' => 'shs',
            'shs' => 'university'
        ];
        
        return $progression[$currentGroup] ?? null;
    }
    
    private function getGradeLevelsForGroup(string $group): array
    {
        $groupMappings = [
            'primary-lower' => ['Primary 1', 'Primary 2', 'Primary 3'],
            'primary-upper' => ['Primary 4', 'Primary 5', 'Primary 6'],
            'jhs' => ['JHS 1', 'JHS 2', 'JHS 3'],
            'shs' => ['SHS 1', 'SHS 2', 'SHS 3'],
        ];
        
        return $groupMappings[$group] ?? [];
    }
    
    private function formatDuration(?int $seconds): string
    {
        if (!$seconds) return 'Unknown';
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;
        
        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $minutes);
        } elseif ($minutes > 0) {
            return sprintf('%d min', $minutes);
        } else {
            return sprintf('%d sec', $remainingSeconds);
        }
    }
    
    private function getLevelDisplayName(string $level): string
    {
        $displayNames = [
            'Primary 1' => 'Primary 1', 'Primary 2' => 'Primary 2', 'Primary 3' => 'Primary 3',
            'Primary 4' => 'Primary 4', 'Primary 5' => 'Primary 5', 'Primary 6' => 'Primary 6',
            'JHS 1' => 'JHS 1', 'JHS 2' => 'JHS 2', 'JHS 3' => 'JHS 3',
            'SHS 1' => 'SHS 1', 'SHS 2' => 'SHS 2', 'SHS 3' => 'SHS 3',
        ];
        
        return $displayNames[$level] ?? ucwords(str_replace('-', ' ', $level));
    }
    
    private function generateCacheKey(int $lessonId, int $userId, array $filters): string
    {
        $filterHash = md5(serialize($filters));
        return "related_lessons_v4:{$lessonId}:{$userId}:{$filterHash}";
    }
    
    private function getCacheTTL(): int
    {
        return config('cache.default') === 'redis' ? 3600 : 1800; // Longer for Redis
    }
    
    private function getScoringBreakdown(array $currentLesson, array $candidate, User $user): array
    {
        return [
            'subject_similarity' => $this->calculateSubjectSimilarity($currentLesson['subject'], $candidate['subject']) * 0.40,
            'difficulty_progression' => $this->lessonDifficultyService->getProgressionScore($currentLesson['level'], $candidate['level']) * 0.25,
            'instructor_consistency' => $this->calculateInstructorConsistency($currentLesson, $candidate) * 0.15,
            'engagement_metrics' => $this->calculateEngagementScore($candidate, $user) * 0.15,
            'learning_patterns' => 0.05, // Placeholder for Phase 1
        ];
    }
    
    private function getUniversityLessons(array $currentLesson, User $user): array
    {
        // For Phase 1, return empty array - will implement in Phase 2
        return [];
    }


}