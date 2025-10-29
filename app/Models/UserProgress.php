<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProgress extends Model
{
    use HasFactory;

    protected $table = 'user_progress';

    protected $fillable = [
        'user_id',
        'current_level',
        'level_group',
        'total_lessons_in_level',
        'completed_lessons',
        'total_quizzes_in_level',
        'completed_quizzes',
        'average_quiz_score',
        'completion_percentage',
        'level_completed',
        'eligible_for_next_level',
        'level_started_at',
        'level_completed_at',
        'total_time_spent_seconds',
        'last_activity_at',
        'current_streak_days',
        'longest_streak_days',
        'performance_metrics',
    ];

    protected $casts = [
        'level_completed' => 'boolean',
        'eligible_for_next_level' => 'boolean',
        'level_started_at' => 'datetime',
        'level_completed_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'performance_metrics' => 'array',
        'average_quiz_score' => 'decimal:2',
        'completion_percentage' => 'decimal:2',
        'total_time_spent_seconds' => 'integer',
        'current_streak_days' => 'integer',
        'longest_streak_days' => 'integer',
    ];

    /**
     * Get the user that owns the progress.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get current progress for a user and level
     */
    public static function getCurrentProgress($userId, $level)
    {
        return static::where('user_id', $userId)
                    ->where('current_level', $level)
                    ->first();
    }

    /**
     * Calculate if user is eligible for next level
     */
    public function calculateEligibility(): bool
    {
        // Get standards for this level group
        $standards = \App\Models\ProgressionStandard::getStandardsForLevel($this->level_group);

        $lessonCompletionRate = $this->total_lessons_in_level > 0
            ? ($this->completed_lessons / $this->total_lessons_in_level) * 100
            : 0;

        $quizCompletionRate = $this->total_quizzes_in_level > 0
            ? ($this->completed_quizzes / $this->total_quizzes_in_level) * 100
            : 0;

        $eligible = $lessonCompletionRate >= $standards['required_lesson_completion_percentage'] &&
                   $quizCompletionRate >= $standards['required_quiz_completion_percentage'] &&
                   $this->average_quiz_score >= $standards['required_average_quiz_score'];

        $this->update(['eligible_for_next_level' => $eligible]);

        return $eligible;
    }

    /**
     * Update completion percentage
     */
    public function updateCompletionPercentage(): void
    {
        $totalItems = $this->total_lessons_in_level + $this->total_quizzes_in_level;
        $completedItems = $this->completed_lessons + $this->completed_quizzes;
        
        $percentage = $totalItems > 0 ? ($completedItems / $totalItems) * 100 : 0;
        
        $this->update(['completion_percentage' => $percentage]);
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'lesson_completion_rate' => $this->total_lessons_in_level > 0
                ? round(($this->completed_lessons / $this->total_lessons_in_level) * 100, 2)
                : 0,
            'quiz_completion_rate' => $this->total_quizzes_in_level > 0
                ? round(($this->completed_quizzes / $this->total_quizzes_in_level) * 100, 2)
                : 0,
            'average_score' => $this->average_quiz_score,
            'overall_completion' => $this->completion_percentage,
            'eligible_for_next' => $this->eligible_for_next_level,
            'time_spent_formatted' => $this->getFormattedTimeSpent(),
            'current_streak' => $this->current_streak_days,
            'longest_streak' => $this->longest_streak_days,
        ];
    }

    /**
     * Record activity and update streaks
     */
    public function recordActivity(): void
    {
        $now = now();
        $today = $now->toDateString();

        // Update last activity
        $this->last_activity_at = $now;

        // Calculate streak
        if ($this->last_activity_at && $this->last_activity_at->toDateString() === $today) {
            // Already recorded today, no change needed
            return;
        }

        $yesterday = $now->copy()->subDay()->toDateString();
        $lastActivityDate = $this->last_activity_at?->toDateString();

        if ($lastActivityDate === $yesterday) {
            // Consecutive day, increment streak
            $this->current_streak_days++;
        } elseif ($lastActivityDate !== $today) {
            // Not consecutive, reset streak
            $this->current_streak_days = 1;
        }

        // Update longest streak if current is higher
        if ($this->current_streak_days > $this->longest_streak_days) {
            $this->longest_streak_days = $this->current_streak_days;
        }

        $this->save();
    }

    /**
     * Add time spent to the level
     */
    public function addTimeSpent(int $seconds): void
    {
        $this->total_time_spent_seconds += $seconds;
        $this->save();
    }

    /**
     * Get formatted time spent
     */
    public function getFormattedTimeSpent(): string
    {
        $hours = floor($this->total_time_spent_seconds / 3600);
        $minutes = floor(($this->total_time_spent_seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    /**
     * Get level duration (time since started) with better formatting
     */
    public function getLevelDuration(): ?string
    {
        if (!$this->level_started_at) {
            return null;
        }

        $now = now();
        $diff = $this->level_started_at->diff($now);

        // More than a week
        if ($diff->days > 7) {
            $weeks = floor($diff->days / 7);
            $remainingDays = $diff->days % 7;

            if ($weeks >= 4) {
                $months = floor($weeks / 4.33); // Approximate months
                return $months === 1 ? "1 month" : "{$months} months";
            }

            if ($remainingDays === 0) {
                return $weeks === 1 ? "1 week" : "{$weeks} weeks";
            }

            return $weeks === 1 ? "1 week {$remainingDays} day" . ($remainingDays > 1 ? 's' : '') : "{$weeks} weeks {$remainingDays} day" . ($remainingDays > 1 ? 's' : '');
        }

        // Days
        if ($diff->days > 0) {
            return $diff->days === 1 ? "1 day" : "{$diff->days} days";
        }

        // Hours
        if ($diff->h > 0) {
            return $diff->h === 1 ? "1 hour" : "{$diff->h} hours";
        }

        // Minutes
        if ($diff->i > 0) {
            return $diff->i === 1 ? "1 minute" : "{$diff->i} minutes";
        }

        return 'Just started';
    }

    /**
     * Get detailed analytics
     */
    public function getDetailedAnalytics(): array
    {
        $lessonStats = \App\Models\LessonCompletion::getLevelStats($this->user_id, $this->current_level);
        $quizStats = \App\Models\QuizAttempt::getLevelStats($this->user_id, $this->current_level);

        return [
            'level_info' => [
                'current_level' => $this->current_level,
                'level_group' => $this->level_group,
                'started_at' => $this->level_started_at,
                'duration' => $this->getLevelDuration(),
                'completed' => $this->level_completed,
            ],
            'lesson_progress' => [
                'total_lessons' => $this->total_lessons_in_level,
                'completed_lessons' => $this->completed_lessons,
                'completion_rate' => $this->total_lessons_in_level > 0
                    ? round(($this->completed_lessons / $this->total_lessons_in_level) * 100, 2)
                    : 0,
                'total_watch_time' => $lessonStats->total_watch_time ?? 0,
            ],
            'quiz_progress' => [
                'total_quizzes' => $this->total_quizzes_in_level,
                'passed_quizzes' => $this->completed_quizzes,
                'completion_rate' => $this->total_quizzes_in_level > 0
                    ? round(($this->completed_quizzes / $this->total_quizzes_in_level) * 100, 2)
                    : 0,
                'average_score' => $this->average_quiz_score,
                'best_score' => $quizStats->best_score ?? 0,
                'total_attempts' => $quizStats->total_attempts ?? 0,
            ],
            'engagement' => [
                'time_spent' => $this->getFormattedTimeSpent(),
                'current_streak' => $this->current_streak_days,
                'longest_streak' => $this->longest_streak_days,
                'last_activity' => $this->last_activity_at,
            ],
            'milestones' => $this->getMilestones(),
        ];
    }

    /**
     * Get achievement milestones
     */
    private function getMilestones(): array
    {
        $milestones = [];

        // Lesson milestones
        if ($this->completed_lessons >= 5) $milestones[] = ['type' => 'lessons', 'count' => 5, 'icon' => '📚', 'title' => 'Lesson Explorer'];
        if ($this->completed_lessons >= 10) $milestones[] = ['type' => 'lessons', 'count' => 10, 'icon' => '🎓', 'title' => 'Knowledge Seeker'];
        if ($this->completed_lessons >= $this->total_lessons_in_level) $milestones[] = ['type' => 'lessons', 'count' => $this->total_lessons_in_level, 'icon' => '🏆', 'title' => 'Lesson Master'];

        // Quiz milestones
        if ($this->completed_quizzes >= 3) $milestones[] = ['type' => 'quizzes', 'count' => 3, 'icon' => '✍️', 'title' => 'Quiz Taker'];
        if ($this->average_quiz_score >= 80) $milestones[] = ['type' => 'quizzes', 'count' => 80, 'icon' => '🎯', 'title' => 'High Scorer'];
        if ($this->completed_quizzes >= $this->total_quizzes_in_level) $milestones[] = ['type' => 'quizzes', 'count' => $this->total_quizzes_in_level, 'icon' => '🏆', 'title' => 'Quiz Champion'];

        // Streak milestones
        if ($this->current_streak_days >= 7) $milestones[] = ['type' => 'streak', 'count' => 7, 'icon' => '🔥', 'title' => 'Week Warrior'];
        if ($this->current_streak_days >= 30) $milestones[] = ['type' => 'streak', 'count' => 30, 'icon' => '🌟', 'title' => 'Monthly Master'];

        return $milestones;
    }
}
