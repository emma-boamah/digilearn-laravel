<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEngagement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content_type', // video, document, quiz, lesson
        'content_id',
        'action', // view, start, complete, like, share, bookmark, pause, resume, skip
        'engagement_score', // 1-10 scale based on action type
        'duration_seconds', // time spent on this action
        'metadata', // additional context data
        'session_id', // to group related actions
        'device_type', // mobile, desktop, tablet
        'user_agent',
        'ip_address',
    ];

    protected $casts = [
        'engagement_score' => 'integer',
        'duration_seconds' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that owns this engagement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record user engagement
     */
    public static function record(
        int $userId,
        string $contentType,
        int $contentId,
        string $action,
        int $durationSeconds = 0,
        array $metadata = [],
        ?string $sessionId = null
    ): self {
        $engagementScore = self::calculateEngagementScore($action, $durationSeconds, $metadata);

        return static::create([
            'user_id' => $userId,
            'content_type' => $contentType,
            'content_id' => $contentId,
            'action' => $action,
            'engagement_score' => $engagementScore,
            'duration_seconds' => $durationSeconds,
            'metadata' => $metadata,
            'session_id' => $sessionId ?: session()->getId(),
            'device_type' => self::detectDeviceType(request()->userAgent()),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Calculate engagement score based on action type and duration
     */
    private static function calculateEngagementScore(string $action, int $durationSeconds, array $metadata): int
    {
        $baseScores = [
            'view' => 1,
            'start' => 2,
            'pause' => 1,
            'resume' => 2,
            'skip' => -1,
            'complete' => 5,
            'like' => 3,
            'share' => 4,
            'bookmark' => 3,
            'download' => 4,
            'comment' => 3,
            'rate' => 2,
            'search' => 1,
            'click' => 1,
        ];

        $score = $baseScores[$action] ?? 1;

        // Bonus for longer engagement
        if ($durationSeconds > 300) { // 5+ minutes
            $score += 2;
        } elseif ($durationSeconds > 60) { // 1+ minute
            $score += 1;
        }

        // Bonus for high completion rates
        if (isset($metadata['completion_percentage']) && $metadata['completion_percentage'] >= 80) {
            $score += 2;
        }

        // Bonus for quiz performance
        if (isset($metadata['score_percentage']) && $metadata['score_percentage'] >= 80) {
            $score += 3;
        }

        return max(1, min(10, $score)); // Clamp between 1-10
    }

    /**
     * Detect device type from user agent
     */
    private static function detectDeviceType(?string $userAgent): string
    {
        if (!$userAgent) return 'unknown';

        $userAgent = strtolower($userAgent);

        if (strpos($userAgent, 'mobile') !== false || strpos($userAgent, 'android') !== false) {
            return 'mobile';
        }

        if (strpos($userAgent, 'tablet') !== false || strpos($userAgent, 'ipad') !== false) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Get engagement statistics for a user
     */
    public static function getUserEngagementStats(int $userId, ?int $days = 30): array
    {
        $query = static::where('user_id', $userId);

        if ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        }

        $stats = $query->selectRaw('
            COUNT(*) as total_actions,
            AVG(engagement_score) as avg_engagement_score,
            SUM(duration_seconds) as total_duration_seconds,
            COUNT(DISTINCT content_id) as unique_content_interacted,
            COUNT(DISTINCT DATE(created_at)) as active_days
        ')->first();

        $contentTypeBreakdown = static::where('user_id', $userId)
            ->when($days, fn($q) => $q->where('created_at', '>=', now()->subDays($days)))
            ->selectRaw('content_type, COUNT(*) as count, AVG(engagement_score) as avg_score')
            ->groupBy('content_type')
            ->get()
            ->keyBy('content_type');

        $actionBreakdown = static::where('user_id', $userId)
            ->when($days, fn($q) => $q->where('created_at', '>=', now()->subDays($days)))
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->get()
            ->keyBy('action');

        return [
            'total_actions' => $stats->total_actions ?? 0,
            'avg_engagement_score' => round($stats->avg_engagement_score ?? 0, 2),
            'total_duration_seconds' => $stats->total_duration_seconds ?? 0,
            'total_duration_formatted' => self::formatDuration($stats->total_duration_seconds ?? 0),
            'unique_content_interacted' => $stats->unique_content_interacted ?? 0,
            'active_days' => $stats->active_days ?? 0,
            'content_type_breakdown' => $contentTypeBreakdown,
            'action_breakdown' => $actionBreakdown,
        ];
    }

    /**
     * Get content recommendations based on user engagement
     */
    public static function getContentRecommendations(int $userId, int $limit = 10): array
    {
        // Get user's most engaged content types
        $preferredContentTypes = static::where('user_id', $userId)
            ->where('engagement_score', '>=', 7)
            ->selectRaw('content_type, COUNT(*) as engagement_count, AVG(engagement_score) as avg_score')
            ->groupBy('content_type')
            ->orderBy('avg_score', 'desc')
            ->orderBy('engagement_count', 'desc')
            ->limit(3)
            ->get();

        // Get user's preferred subjects from UserPreference
        $preferredSubjects = UserPreference::getSubjectPreferences($userId)
            ->pluck('preference_value')
            ->toArray();

        $recommendations = [];

        // Generate recommendations based on engagement patterns
        foreach ($preferredContentTypes as $contentType) {
            $contentRecommendations = static::getRecommendationsByContentType(
                $userId,
                $contentType->content_type,
                $preferredSubjects,
                $limit
            );

            $recommendations = array_merge($recommendations, $contentRecommendations);
        }

        // Remove duplicates and limit results
        $recommendations = array_unique($recommendations, SORT_REGULAR);
        return array_slice($recommendations, 0, $limit);
    }

    /**
     * Get recommendations for specific content type
     */
    private static function getRecommendationsByContentType(int $userId, string $contentType, array $preferredSubjects, int $limit): array
    {
        $recommendations = [];

        switch ($contentType) {
            case 'video':
                // Recommend videos in preferred subjects that user hasn't watched
                $watchedVideoIds = static::where('user_id', $userId)
                    ->where('content_type', 'video')
                    ->pluck('content_id')
                    ->toArray();

                $videos = \App\Models\Video::whereNotIn('id', $watchedVideoIds)
                    ->whereIn('subject', $preferredSubjects)
                    ->orderBy('views', 'desc')
                    ->limit($limit)
                    ->get();

                foreach ($videos as $video) {
                    $recommendations[] = [
                        'type' => 'video',
                        'id' => $video->id,
                        'title' => $video->title,
                        'subject' => $video->subject,
                        'reason' => 'Based on your subject preferences and viewing history',
                        'score' => 8,
                    ];
                }
                break;

            case 'quiz':
                // Recommend quizzes in preferred subjects
                $attemptedQuizIds = static::where('user_id', $userId)
                    ->where('content_type', 'quiz')
                    ->pluck('content_id')
                    ->toArray();

                $quizzes = \App\Models\Quiz::whereNotIn('id', $attemptedQuizIds)
                    ->whereIn('subject', $preferredSubjects)
                    ->orderBy('attempts_count', 'desc')
                    ->limit($limit)
                    ->get();

                foreach ($quizzes as $quiz) {
                    $recommendations[] = [
                        'type' => 'quiz',
                        'id' => $quiz->id,
                        'title' => $quiz->title,
                        'subject' => $quiz->subject?->name,
                        'reason' => 'Practice quiz in your preferred subject',
                        'score' => 7,
                    ];
                }
                break;

            case 'document':
                // Recommend documents in preferred subjects
                $viewedDocumentIds = static::where('user_id', $userId)
                    ->where('content_type', 'document')
                    ->pluck('content_id')
                    ->toArray();

                $documents = \App\Models\Document::whereNotIn('id', $viewedDocumentIds)
                    ->whereIn('subject', $preferredSubjects)
                    ->orderBy('views', 'desc')
                    ->limit($limit)
                    ->get();

                foreach ($documents as $document) {
                    $recommendations[] = [
                        'type' => 'document',
                        'id' => $document->id,
                        'title' => $document->title,
                        'subject' => $document->subject,
                        'reason' => 'Study material in your preferred subject',
                        'score' => 6,
                    ];
                }
                break;
        }

        return $recommendations;
    }

    /**
     * Format duration in seconds to human readable format
     */
    private static function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    /**
     * Get engagement trends over time
     */
    public static function getEngagementTrends(int $userId, int $days = 30): array
    {
        $trends = static::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as actions, AVG(engagement_score) as avg_score, SUM(duration_seconds) as total_duration')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill missing dates with zeros
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $result[$date] = $trends->get($date, [
                'date' => $date,
                'actions' => 0,
                'avg_score' => 0,
                'total_duration' => 0,
            ]);
        }

        return array_values($result);
    }

    /**
     * Scope for filtering by content type
     */
    public function scopeByContentType($query, string $contentType)
    {
        return $query->where('content_type', $contentType);
    }

    /**
     * Scope for filtering by action
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for high engagement actions
     */
    public function scopeHighEngagement($query)
    {
        return $query->where('engagement_score', '>=', 7);
    }
}