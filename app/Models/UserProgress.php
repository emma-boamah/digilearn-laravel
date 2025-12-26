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
     * Check if user should progress to next individual level within the current level group
     */
    public function shouldProgressWithinLevelGroup(): ?string
    {
        // Get standards for individual level progression (lower thresholds)
        $standards = \App\Models\ProgressionStandard::getStandardsForLevel($this->level_group);

        // Check individual level completion thresholds (lower than group progression)
        $lessonCompletionRate = $this->total_lessons_in_level > 0
            ? ($this->completed_lessons / $this->total_lessons_in_level) * 100
            : 0;

        $quizCompletionRate = $this->total_quizzes_in_level > 0
            ? ($this->completed_quizzes / $this->total_quizzes_in_level) * 100
            : 0;

        // Use individual level thresholds (lower than group progression thresholds)
        $individualLessonThreshold = $standards['individual_level_lesson_threshold'] ?? 75.00;
        $individualQuizThreshold = $standards['individual_level_quiz_threshold'] ?? 60.00;
        $individualScoreThreshold = $standards['individual_level_score_threshold'] ?? 65.00;

        $eligibleForIndividualProgression = $lessonCompletionRate >= $individualLessonThreshold &&
                                           $quizCompletionRate >= $individualQuizThreshold &&
                                           $this->average_quiz_score >= $individualScoreThreshold;

        if (!$eligibleForIndividualProgression) {
            return null;
        }

        // Get the next individual level within the current level group
        $nextLevel = $this->getNextLevelWithinGroup();

        if ($nextLevel) {
            return $nextLevel;
        }

        // If no next level within group, return null (ready for level group progression)
        return null;
    }

    /**
     * Get the next individual level within the current level group
     */
    private function getNextLevelWithinGroup(): ?string
    {
        $levelProgression = [
            'primary-lower' => [
                'Primary 1' => 'Primary 2',
                'Primary 2' => 'Primary 3',
                'Primary 3' => null, // End of group, ready for primary-upper
            ],
            'primary-upper' => [
                'Primary 4' => 'Primary 5',
                'Primary 5' => 'Primary 6',
                'Primary 6' => null, // End of group, ready for jhs
            ],
            'jhs' => [
                'JHS 1' => 'JHS 2',
                'JHS 2' => 'JHS 3',
                'JHS 3' => null, // End of group, ready for shs
            ],
            'shs' => [
                'SHS 1' => 'SHS 2',
                'SHS 2' => 'SHS 3',
                'SHS 3' => null, // End of group, no more progression
            ],
            'university' => [
                'University Year 1' => 'University Year 2',
                'University Year 2' => 'University Year 3',
                'University Year 3' => 'University Year 4',
                'University Year 4' => null, // End of university progression
            ],
        ];

        // Get user's current grade from the user model
        $user = $this->user;
        if (!$user || !isset($levelProgression[$this->level_group])) {
            return null;
        }

        $currentGrade = $user->grade;

        // Check if current grade exists in the progression map
        if (isset($levelProgression[$this->level_group][$currentGrade])) {
            return $levelProgression[$this->level_group][$currentGrade];
        }

        return null;
    }

    /**
     * Progress user to next individual level within the current level group
     */
    public function progressWithinLevelGroup(): bool
    {
        $nextLevel = $this->shouldProgressWithinLevelGroup();

        if (!$nextLevel) {
            return false; // No progression within group needed/possible
        }

        // Update user's grade to the next level
        $user = $this->user;
        if ($user) {
            $user->update(['grade' => $nextLevel]);

            // Reset progress for the new individual level
            $this->resetForNewLevel($nextLevel);

            \Illuminate\Support\Facades\Log::info('User progressed within level group', [
                'user_id' => $this->user_id,
                'from_grade' => $user->getOriginal('grade'),
                'to_grade' => $nextLevel,
                'level_group' => $this->level_group,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Reset progress tracking for a new individual level
     */
    private function resetForNewLevel(string $newLevel): void
    {
        // Get counts for the new level
        $levelMappings = [
            'primary-lower' => ['Primary 1', 'Primary 2', 'Primary 3'],
            'primary-upper' => ['Primary 4', 'Primary 5', 'Primary 6'],
            'jhs' => ['JHS 1', 'JHS 2', 'JHS 3'],
            'shs' => ['SHS 1', 'SHS 2', 'SHS 3'],
            'university' => ['University Year 1', 'University Year 2', 'University Year 3', 'University Year 4'],
        ];

        $gradeLevels = $levelMappings[$this->level_group] ?? [$newLevel];

        // Get actual counts from database for this specific grade level
        $totalLessons = \App\Models\Video::approved()
            ->where('grade_level', $newLevel)
            ->count();

        $totalQuizzes = \App\Models\Quiz::where('grade_level', $newLevel)
            ->count();

        // Reset progress for the new level
        $this->update([
            'completed_lessons' => 0,
            'completed_quizzes' => 0,
            'average_quiz_score' => 0,
            'completion_percentage' => 0,
            'level_completed' => false,
            'eligible_for_next_level' => false,
            'level_started_at' => now(),
            'level_completed_at' => null,
            'total_time_spent_seconds' => 0,
            'current_streak_days' => 0,
            'total_lessons_in_level' => $totalLessons,
            'total_quizzes_in_level' => $totalQuizzes,
        ]);
    }

    /**
     * Update completion percentage
     */
    public function updateCompletionPercentage(): void
    {
        // Get progression standards for this level group
        $standards = \App\Models\ProgressionStandard::getStandardsForLevel($this->level_group);

        // Calculate actual completion rates
        $lessonCompletionRate = $this->total_lessons_in_level > 0
            ? ($this->completed_lessons / $this->total_lessons_in_level) * 100
            : 0;

        $quizCompletionRate = $this->total_quizzes_in_level > 0
            ? ($this->completed_quizzes / $this->total_quizzes_in_level) * 100
            : 0;

        // Calculate how close user is to meeting each requirement
        $lessonProgress = min(100, ($lessonCompletionRate / $standards['required_lesson_completion_percentage']) * 100);
        $quizProgress = min(100, ($quizCompletionRate / $standards['required_quiz_completion_percentage']) * 100);
        $scoreProgress = min(100, ($this->average_quiz_score / $standards['required_average_quiz_score']) * 100);

        // Overall completion is the minimum of the three requirements
        // This ensures 100% only when ALL requirements are met
        $percentage = min($lessonProgress, $quizProgress, $scoreProgress);

        $this->update(['completion_percentage' => $percentage]);
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        // Get standards for threshold comparison
        $standards = \App\Models\ProgressionStandard::getStandardsForLevel($this->level_group);

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
            'eligible_for_individual_progression' => $this->shouldProgressWithinLevelGroup() !== null,
            'time_spent_formatted' => $this->getFormattedTimeSpent(),
            'current_streak' => $this->current_streak_days,
            'longest_streak' => $this->longest_streak_days,
            // Threshold information for transparency
            'thresholds' => [
                'group_progression' => [
                    'lesson_completion_required' => $standards['required_lesson_completion_percentage'],
                    'quiz_completion_required' => $standards['required_quiz_completion_percentage'],
                    'average_score_required' => $standards['required_average_quiz_score'],
                ],
                'individual_progression' => [
                    'lesson_completion_required' => $standards['individual_level_lesson_threshold'] ?? 75.00,
                    'quiz_completion_required' => $standards['individual_level_quiz_threshold'] ?? 60.00,
                    'average_score_required' => $standards['individual_level_score_threshold'] ?? 65.00,
                ],
            ],
        ];
    }

    /**
     * Record activity and update streaks
     */
    public function recordActivity(): void
    {
        $now = now();
        $today = $now->toDateString();
        $yesterday = $now->copy()->subDay()->toDateString();
        $lastActivityDate = $this->last_activity_at?->toDateString();

        // Always update last activity timestamp
        $this->last_activity_at = $now;

        // Only update streak logic if this is the first activity today
        if ($lastActivityDate !== $today) {
            if ($lastActivityDate === $yesterday) {
                // Consecutive day, increment streak
                $this->current_streak_days++;
            } elseif ($lastActivityDate && $lastActivityDate !== $yesterday) {
                // Not consecutive, reset streak to 1
                $this->current_streak_days = 1;
            } else {
                // First activity ever or streak was broken, start with 1
                $this->current_streak_days = 1;
            }

            // Update longest streak if current is higher
            if ($this->current_streak_days > $this->longest_streak_days) {
                $this->longest_streak_days = $this->current_streak_days;
            }
        }
        // If already active today, keep the existing streak

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
