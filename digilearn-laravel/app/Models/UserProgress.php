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
        'performance_metrics',
    ];

    protected $casts = [
        'level_completed' => 'boolean',
        'eligible_for_next_level' => 'boolean',
        'level_started_at' => 'datetime',
        'level_completed_at' => 'datetime',
        'performance_metrics' => 'array',
        'average_quiz_score' => 'decimal:2',
        'completion_percentage' => 'decimal:2',
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
        // Criteria for progression:
        // 1. At least 80% of lessons completed
        // 2. At least 70% of quizzes completed
        // 3. Average quiz score of at least 70%
        
        $lessonCompletionRate = $this->total_lessons_in_level > 0 
            ? ($this->completed_lessons / $this->total_lessons_in_level) * 100 
            : 0;
            
        $quizCompletionRate = $this->total_quizzes_in_level > 0 
            ? ($this->completed_quizzes / $this->total_quizzes_in_level) * 100 
            : 0;

        $eligible = $lessonCompletionRate >= 80 && 
                   $quizCompletionRate >= 70 && 
                   $this->average_quiz_score >= 70;

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
        ];
    }
}
