<?php

namespace App\Services;

use App\Models\UserEngagement;
use App\Models\UserPreference;
use App\Models\Video;
use App\Models\Quiz;
use App\Models\Document;
use App\Models\Course;
use Illuminate\Support\Collection;

class RecommendationService
{
    /**
     * Get personalized content recommendations for a user
     */
    public function getRecommendations(int $userId, int $limit = 10): array
    {
        // Get user's engagement patterns
        $engagementStats = UserEngagement::getUserEngagementStats($userId);

        // Get user's subject preferences
        $preferredSubjects = UserPreference::getSubjectPreferences($userId)
            ->pluck('preference_value')
            ->toArray();

        $recommendations = [];

        // Generate recommendations based on different strategies
        $recommendations = array_merge($recommendations, $this->getEngagementBasedRecommendations($userId, $preferredSubjects, $limit));
        $recommendations = array_merge($recommendations, $this->getCollaborativeRecommendations($userId, $preferredSubjects, $limit));
        $recommendations = array_merge($recommendations, $this->getContentBasedRecommendations($userId, $preferredSubjects, $limit));
        $recommendations = array_merge($recommendations, $this->getTrendingRecommendations($userId, $preferredSubjects, $limit));

        // Remove duplicates and sort by relevance score
        $recommendations = $this->deduplicateAndSort($recommendations);

        return array_slice($recommendations, 0, $limit);
    }

    /**
     * Get recommendations based on user's engagement history
     */
    private function getEngagementBasedRecommendations(int $userId, array $preferredSubjects, int $limit): array
    {
        $recommendations = [];

        // Get user's most engaged content types
        $topContentTypes = UserEngagement::where('user_id', $userId)
            ->where('engagement_score', '>=', 7)
            ->selectRaw('content_type, COUNT(*) as count, AVG(engagement_score) as avg_score')
            ->groupBy('content_type')
            ->orderBy('avg_score', 'desc')
            ->limit(3)
            ->get();

        foreach ($topContentTypes as $contentType) {
            $contentRecommendations = $this->getRecommendationsByContentType(
                $userId,
                $contentType->content_type,
                $preferredSubjects,
                $limit
            );

            $recommendations = array_merge($recommendations, $contentRecommendations);
        }

        return $recommendations;
    }

    /**
     * Get collaborative filtering recommendations
     */
    private function getCollaborativeRecommendations(int $userId, array $preferredSubjects, int $limit): array
    {
        $recommendations = [];

        // Find similar users based on subject preferences and engagement patterns
        $similarUsers = $this->findSimilarUsers($userId, $preferredSubjects);

        if (!empty($similarUsers)) {
            // Get content that similar users engaged with highly but current user hasn't
            $similarUserIds = collect($similarUsers)->pluck('user_id')->toArray();

            $highlyEngagedContent = UserEngagement::whereIn('user_id', $similarUserIds)
                ->where('engagement_score', '>=', 8)
                ->whereNotIn('content_id', function($query) use ($userId) {
                    $query->select('content_id')
                          ->from('user_engagements')
                          ->where('user_id', $userId);
                })
                ->selectRaw('content_type, content_id, AVG(engagement_score) as avg_score, COUNT(*) as engagement_count')
                ->groupBy('content_type', 'content_id')
                ->having('engagement_count', '>=', 2)
                ->orderBy('avg_score', 'desc')
                ->limit($limit * 2)
                ->get();

            foreach ($highlyEngagedContent as $content) {
                $contentDetails = $this->getContentDetails($content->content_type, $content->content_id);
                if ($contentDetails) {
                    $recommendations[] = [
                        'type' => $content->content_type,
                        'id' => $content->content_id,
                        'title' => $contentDetails['title'],
                        'subject' => $contentDetails['subject'] ?? 'General',
                        'reason' => 'Users with similar interests enjoyed this',
                        'score' => min(10, $content->avg_score + 1),
                        'strategy' => 'collaborative',
                    ];
                }
            }
        }

        return $recommendations;
    }

    /**
     * Get content-based recommendations
     */
    private function getContentBasedRecommendations(int $userId, array $preferredSubjects, int $limit): array
    {
        $recommendations = [];

        // Get content in user's preferred subjects that they haven't engaged with
        foreach (['video', 'quiz', 'document'] as $contentType) {
            $engagedContentIds = UserEngagement::where('user_id', $userId)
                ->where('content_type', $contentType)
                ->pluck('content_id')
                ->toArray();

            $newContent = $this->getNewContentByType($contentType, $engagedContentIds, $preferredSubjects, $limit);

            foreach ($newContent as $content) {
                $recommendations[] = [
                    'type' => $contentType,
                    'id' => $content['id'],
                    'title' => $content['title'],
                    'subject' => $content['subject'] ?? 'General',
                    'reason' => 'New content in your preferred subjects',
                    'score' => 6,
                    'strategy' => 'content_based',
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Get trending/popular content recommendations
     */
    private function getTrendingRecommendations(int $userId, array $preferredSubjects, int $limit): array
    {
        $recommendations = [];

        // Get trending content from the last 7 days
        $trendingContent = UserEngagement::where('created_at', '>=', now()->subDays(7))
            ->where('engagement_score', '>=', 6)
            ->whereNotIn('content_id', function($query) use ($userId) {
                $query->select('content_id')
                      ->from('user_engagements')
                      ->where('user_id', $userId);
            })
            ->selectRaw('content_type, content_id, COUNT(*) as popularity, AVG(engagement_score) as avg_score')
            ->groupBy('content_type', 'content_id')
            ->having('popularity', '>=', 3)
            ->orderBy('popularity', 'desc')
            ->orderBy('avg_score', 'desc')
            ->limit($limit)
            ->get();

        foreach ($trendingContent as $content) {
            $contentDetails = $this->getContentDetails($content->content_type, $content->content_id);
            if ($contentDetails) {
                $recommendations[] = [
                    'type' => $content->content_type,
                    'id' => $content->content_id,
                    'title' => $contentDetails['title'],
                    'subject' => $contentDetails['subject'] ?? 'General',
                    'reason' => 'Trending content this week',
                    'score' => min(9, $content->avg_score + 0.5),
                    'strategy' => 'trending',
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Find users with similar preferences and engagement patterns
     */
    private function findSimilarUsers(int $userId, array $preferredSubjects): array
    {
        // Find users with similar subject preferences
        $similarUsersByPreferences = UserPreference::where('preference_type', 'subject')
            ->whereIn('preference_value', $preferredSubjects)
            ->where('user_id', '!=', $userId)
            ->selectRaw('user_id, COUNT(*) as matching_preferences')
            ->groupBy('user_id')
            ->having('matching_preferences', '>=', 1)
            ->orderBy('matching_preferences', 'desc')
            ->limit(10)
            ->get();

        return $similarUsersByPreferences->toArray();
    }

    /**
     * Get recommendations for specific content type
     */
    private function getRecommendationsByContentType(int $userId, string $contentType, array $preferredSubjects, int $limit): array
    {
        $engagedContentIds = UserEngagement::where('user_id', $userId)
            ->where('content_type', $contentType)
            ->pluck('content_id')
            ->toArray();

        return $this->getNewContentByType($contentType, $engagedContentIds, $preferredSubjects, $limit);
    }

    /**
     * Get new content by type that user hasn't engaged with
     */
    private function getNewContentByType(string $contentType, array $engagedContentIds, array $preferredSubjects, int $limit): array
    {
        switch ($contentType) {
            case 'video':
                $query = Video::whereNotIn('id', $engagedContentIds);
                if (!empty($preferredSubjects)) {
                    $query->whereIn('subject', $preferredSubjects);
                }
                return $query->orderBy('views', 'desc')
                    ->limit($limit)
                    ->get(['id', 'title', 'subject'])
                    ->toArray();

            case 'quiz':
                $query = Quiz::whereNotIn('id', $engagedContentIds);
                if (!empty($preferredSubjects)) {
                    $query->whereIn('subject', $preferredSubjects);
                }
                return $query->orderBy('attempts_count', 'desc')
                    ->limit($limit)
                    ->get(['id', 'title', 'subject'])
                    ->toArray();

            case 'document':
                $query = Document::whereNotIn('id', $engagedContentIds);
                if (!empty($preferredSubjects)) {
                    $query->whereIn('subject', $preferredSubjects);
                }
                return $query->orderBy('views', 'desc')
                    ->limit($limit)
                    ->get(['id', 'title', 'subject'])
                    ->toArray();

            default:
                return [];
        }
    }

    /**
     * Get content details by type and ID
     */
    private function getContentDetails(string $contentType, int $contentId): ?array
    {
        switch ($contentType) {
            case 'video':
                $content = Video::find($contentId);
                return $content ? ['title' => $content->title, 'subject' => $content->subject] : null;

            case 'quiz':
                $content = Quiz::find($contentId);
                return $content ? ['title' => $content->title, 'subject' => $content->subject] : null;

            case 'document':
                $content = Document::find($contentId);
                return $content ? ['title' => $content->title, 'subject' => $content->subject] : null;

            default:
                return null;
        }
    }

    /**
     * Remove duplicates and sort recommendations by score
     */
    private function deduplicateAndSort(array $recommendations): array
    {
        $unique = [];
        $seen = [];

        foreach ($recommendations as $rec) {
            $key = $rec['type'] . '-' . $rec['id'];
            if (!in_array($key, $seen)) {
                $seen[] = $key;
                $unique[] = $rec;
            }
        }

        // Sort by score descending
        usort($unique, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $unique;
    }

    /**
     * Get user engagement analytics
     */
    public function getUserAnalytics(int $userId): array
    {
        $engagementStats = UserEngagement::getUserEngagementStats($userId);
        $engagementTrends = UserEngagement::getEngagementTrends($userId);

        // Get learning patterns
        $learningPatterns = $this->analyzeLearningPatterns($userId);

        return [
            'engagement_stats' => $engagementStats,
            'engagement_trends' => $engagementTrends,
            'learning_patterns' => $learningPatterns,
            'recommendations' => $this->getRecommendations($userId),
        ];
    }

    /**
     * Analyze user's learning patterns
     */
    private function analyzeLearningPatterns(int $userId): array
    {
        $patterns = [];

        // Best performing subjects
        $subjectPerformance = UserEngagement::where('user_id', $userId)
            ->where('content_type', 'quiz')
            ->join('quiz_attempts', function($join) {
                $join->on('user_engagements.content_id', '=', 'quiz_attempts.quiz_id')
                     ->on('user_engagements.user_id', '=', 'quiz_attempts.user_id');
            })
            ->selectRaw('quiz_attempts.quiz_subject as subject, AVG(quiz_attempts.score_percentage) as avg_score')
            ->groupBy('subject')
            ->orderBy('avg_score', 'desc')
            ->get();

        $patterns['best_subjects'] = $subjectPerformance->take(3)->toArray();

        // Preferred content types
        $contentTypePreferences = UserEngagement::where('user_id', $userId)
            ->selectRaw('content_type, COUNT(*) as count, AVG(engagement_score) as avg_score')
            ->groupBy('content_type')
            ->orderBy('avg_score', 'desc')
            ->get();

        $patterns['preferred_content_types'] = $contentTypePreferences->toArray();

        // Learning time patterns
        $timePatterns = UserEngagement::where('user_id', $userId)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count, AVG(engagement_score) as avg_score')
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->get();

        $patterns['peak_learning_hours'] = $timePatterns->take(3)->toArray();

        return $patterns;
    }
}