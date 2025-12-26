<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RecommendationService;
use App\Models\UserEngagement;
use App\Models\UserPreference;

class RecommendationController extends Controller
{
    protected $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    /**
     * Get personalized recommendation feeds for the dashboard
     */
    public function getDashboardFeeds()
    {
        $userId = Auth::id();

        $feeds = [
            'recommended_for_you' => $this->getRecommendedForYou($userId),
            'trending_in_subjects' => $this->getTrendingInSubjects($userId),
            'continue_learning' => $this->getContinueLearning($userId),
            'popular_this_week' => $this->getPopularThisWeek($userId),
            'based_on_interests' => $this->getBasedOnInterests($userId),
            'learning_streak' => $this->getLearningStreakInfo($userId),
        ];

        return response()->json($feeds);
    }

    /**
     * Get "Recommended for You" feed
     */
    private function getRecommendedForYou(int $userId): array
    {
        $recommendations = $this->recommendationService->getRecommendations($userId, 6);

        return [
            'title' => 'Recommended for You',
            'description' => 'Personalized content based on your learning patterns',
            'items' => array_slice($recommendations, 0, 6),
            'total_count' => count($recommendations),
        ];
    }

    /**
     * Get "Trending in Your Subjects" feed
     */
    private function getTrendingInSubjects(int $userId): array
    {
        $preferredSubjects = UserPreference::getSubjectPreferences($userId)
            ->pluck('preference_value')
            ->toArray();

        if (empty($preferredSubjects)) {
            return [
                'title' => 'Trending Content',
                'description' => 'Popular content across all subjects',
                'items' => $this->getPopularContent(6),
                'total_count' => 0,
            ];
        }

        // Get trending content in user's preferred subjects
        $trendingContent = UserEngagement::where('created_at', '>=', now()->subDays(7))
            ->where('engagement_score', '>=', 6)
            ->whereHas('user', function($query) use ($userId) {
                $query->where('id', '!=', $userId); // Exclude current user's own activity
            })
            ->join('videos', function($join) {
                $join->on('user_engagements.content_id', '=', 'videos.id')
                     ->where('user_engagements.content_type', '=', 'video');
            })
            ->whereIn('videos.subject', $preferredSubjects)
            ->selectRaw('
                user_engagements.content_type,
                user_engagements.content_id,
                videos.title,
                videos.subject,
                COUNT(*) as popularity,
                AVG(user_engagements.engagement_score) as avg_score,
                MAX(user_engagements.created_at) as latest_activity
            ')
            ->groupBy('user_engagements.content_type', 'user_engagements.content_id', 'videos.title', 'videos.subject')
            ->having('popularity', '>=', 2)
            ->orderBy('popularity', 'desc')
            ->orderBy('avg_score', 'desc')
            ->limit(6)
            ->get();

        $items = $trendingContent->map(function($item) {
            return [
                'type' => $item->content_type,
                'id' => $item->content_id,
                'title' => $item->title,
                'subject' => $item->subject,
                'reason' => 'Trending in ' . $item->subject,
                'score' => min(10, $item->avg_score + 0.5),
                'popularity' => $item->popularity,
                'strategy' => 'trending_subjects',
            ];
        })->toArray();

        return [
            'title' => 'Trending in Your Subjects',
            'description' => 'What\'s popular in ' . implode(', ', array_slice($preferredSubjects, 0, 3)),
            'items' => $items,
            'total_count' => count($items),
        ];
    }

    /**
     * Get "Continue Learning" feed - incomplete content
     */
    private function getContinueLearning(int $userId): array
    {
        // Get lessons with low completion but recent activity
        $continueItems = \App\Models\LessonCompletion::where('user_id', $userId)
            ->where('completion_percentage', '<', 100)
            ->where('completion_percentage', '>', 10) // At least 10% complete
            ->where('last_watched_at', '>=', now()->subDays(30))
            ->orderBy('last_watched_at', 'desc')
            ->limit(4)
            ->get();

        $items = $continueItems->map(function($completion) {
            return [
                'type' => 'lesson',
                'id' => $completion->lesson_id,
                'title' => $completion->lesson_title,
                'subject' => $completion->lesson_subject,
                'progress' => $completion->completion_percentage,
                'last_activity' => $completion->last_watched_at->diffForHumans(),
                'reason' => 'Continue where you left off',
                'score' => 8,
                'strategy' => 'continue_learning',
            ];
        })->toArray();

        // Also include incomplete quizzes
        $incompleteQuizzes = \App\Models\QuizAttempt::where('user_id', $userId)
            ->where('passed', false)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();

        $quizItems = $incompleteQuizzes->map(function($attempt) {
            return [
                'type' => 'quiz',
                'id' => $attempt->quiz_id,
                'title' => $attempt->quiz_title,
                'subject' => $attempt->quiz_subject,
                'progress' => $attempt->score_percentage,
                'last_activity' => $attempt->completed_at->diffForHumans(),
                'reason' => 'Try again to improve your score',
                'score' => 7,
                'strategy' => 'continue_learning',
            ];
        })->toArray();

        $allItems = array_merge($items, $quizItems);

        return [
            'title' => 'Continue Learning',
            'description' => 'Pick up where you left off',
            'items' => array_slice($allItems, 0, 6),
            'total_count' => count($allItems),
        ];
    }

    /**
     * Get "Popular This Week" feed
     */
    private function getPopularThisWeek(int $userId): array
    {
        $popularContent = UserEngagement::where('created_at', '>=', now()->subDays(7))
            ->where('engagement_score', '>=', 5)
            ->selectRaw('
                content_type,
                content_id,
                COUNT(*) as popularity,
                AVG(engagement_score) as avg_score,
                COUNT(DISTINCT user_id) as unique_users
            ')
            ->groupBy('content_type', 'content_id')
            ->having('popularity', '>=', 3)
            ->having('unique_users', '>=', 2)
            ->orderBy('popularity', 'desc')
            ->orderBy('avg_score', 'desc')
            ->limit(6)
            ->get();

        $items = [];
        foreach ($popularContent as $content) {
            $contentDetails = $this->getContentDetails($content->content_type, $content->content_id);
            if ($contentDetails) {
                $items[] = [
                    'type' => $content->content_type,
                    'id' => $content->content_id,
                    'title' => $contentDetails['title'],
                    'subject' => $contentDetails['subject'] ?? 'General',
                    'reason' => 'Popular this week',
                    'score' => min(9, $content->avg_score + 0.5),
                    'popularity' => $content->popularity,
                    'unique_users' => $content->unique_users,
                    'strategy' => 'popular_week',
                ];
            }
        }

        return [
            'title' => 'Popular This Week',
            'description' => 'What everyone\'s learning right now',
            'items' => $items,
            'total_count' => count($items),
        ];
    }

    /**
     * Get "Based on Your Interests" feed
     */
    private function getBasedOnInterests(int $userId): array
    {
        $preferredSubjects = UserPreference::getSubjectPreferences($userId)
            ->pluck('preference_value')
            ->toArray();

        if (empty($preferredSubjects)) {
            return [
                'title' => 'Explore New Topics',
                'description' => 'Set your preferences to get personalized recommendations',
                'items' => [],
                'total_count' => 0,
            ];
        }

        // Get highly rated content in preferred subjects that user hasn't engaged with
        $engagedContentIds = UserEngagement::where('user_id', $userId)
            ->pluck('content_id')
            ->toArray();

        $interestBasedContent = [];

        // Get videos in preferred subjects
        $videos = \App\Models\Video::whereNotIn('id', $engagedContentIds)
            ->whereIn('subject', $preferredSubjects)
            ->where('is_featured', true)
            ->orderBy('views', 'desc')
            ->limit(3)
            ->get();

        foreach ($videos as $video) {
            $interestBasedContent[] = [
                'type' => 'video',
                'id' => $video->id,
                'title' => $video->title,
                'subject' => $video->subject,
                'reason' => 'Featured in ' . $video->subject,
                'score' => 7,
                'strategy' => 'interest_based',
            ];
        }

        // Get quizzes in preferred subjects
        $quizzes = \App\Models\Quiz::whereNotIn('id', $engagedContentIds)
            ->whereIn('subject', $preferredSubjects)
            ->orderBy('attempts_count', 'desc')
            ->limit(3)
            ->get();

        foreach ($quizzes as $quiz) {
            $interestBasedContent[] = [
                'type' => 'quiz',
                'id' => $quiz->id,
                'title' => $quiz->title,
                'subject' => $quiz->subject?->name,
                'reason' => 'Practice ' . ($quiz->subject?->name ?? 'various subjects'),
                'score' => 6,
                'strategy' => 'interest_based',
            ];
        }

        return [
            'title' => 'Based on Your Interests',
            'description' => 'Content aligned with your preferred subjects',
            'items' => array_slice($interestBasedContent, 0, 6),
            'total_count' => count($interestBasedContent),
        ];
    }

    /**
     * Get learning streak information
     */
    private function getLearningStreakInfo(int $userId): array
    {
        $progress = \App\Models\UserProgress::where('user_id', $userId)
            ->where('current_level', session('selected_level_group', 'primary-lower'))
            ->first();

        if (!$progress) {
            return [
                'current_streak' => 0,
                'longest_streak' => 0,
                'message' => 'Start learning to build your streak!',
            ];
        }

        $streak = $progress->current_streak_days;
        $longest = $progress->longest_streak_days;

        $messages = [
            0 => 'Start learning to build your streak!',
            1 => 'Great start! Keep it up!',
            7 => 'One week strong! You\'re on fire!',
            14 => 'Two weeks in a row! Amazing dedication!',
            30 => 'Monthly champion! You\'re unstoppable!',
        ];

        $message = $messages[$streak] ?? "Incredible! {$streak} days in a row!";

        return [
            'current_streak' => $streak,
            'longest_streak' => $longest,
            'message' => $message,
            'badge' => $this->getStreakBadge($streak),
        ];
    }

    /**
     * Get streak badge based on streak length
     */
    private function getStreakBadge(int $streak): string
    {
        if ($streak >= 30) return '🏆';
        if ($streak >= 14) return '🌟';
        if ($streak >= 7) return '🔥';
        if ($streak >= 3) return '⚡';
        return '🌱';
    }

    /**
     * Get popular content across all subjects (fallback)
     */
    private function getPopularContent(int $limit): array
    {
        $popularContent = UserEngagement::where('created_at', '>=', now()->subDays(7))
            ->where('engagement_score', '>=', 5)
            ->selectRaw('
                content_type,
                content_id,
                COUNT(*) as popularity,
                AVG(engagement_score) as avg_score
            ')
            ->groupBy('content_type', 'content_id')
            ->orderBy('popularity', 'desc')
            ->orderBy('avg_score', 'desc')
            ->limit($limit)
            ->get();

        $items = [];
        foreach ($popularContent as $content) {
            $contentDetails = $this->getContentDetails($content->content_type, $content->content_id);
            if ($contentDetails) {
                $items[] = [
                    'type' => $content->content_type,
                    'id' => $content->content_id,
                    'title' => $contentDetails['title'],
                    'subject' => $contentDetails['subject'] ?? 'General',
                    'reason' => 'Popular content',
                    'score' => $content->avg_score,
                    'popularity' => $content->popularity,
                    'strategy' => 'popular',
                ];
            }
        }

        return $items;
    }

    /**
     * Get content details by type and ID
     */
    private function getContentDetails(string $contentType, int $contentId): ?array
    {
        try {
            switch ($contentType) {
                case 'video':
                    $content = \App\Models\Video::find($contentId);
                    return $content ? ['title' => $content->title, 'subject' => $content->subject] : null;

                case 'quiz':
                    $content = \App\Models\Quiz::find($contentId);
                    return $content ? ['title' => $content->title, 'subject' => $content->subject] : null;

                case 'document':
                    $content = \App\Models\Document::find($contentId);
                    return $content ? ['title' => $content->title, 'subject' => $content->subject] : null;

                case 'lesson':
                    $completion = \App\Models\LessonCompletion::where('lesson_id', $contentId)->first();
                    return $completion ? ['title' => $completion->lesson_title, 'subject' => $completion->lesson_subject] : null;

                default:
                    return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get analytics data for the user
     */
    public function getAnalytics()
    {
        $userId = Auth::id();
        $analytics = $this->recommendationService->getUserAnalytics($userId);

        return response()->json($analytics);
    }
}