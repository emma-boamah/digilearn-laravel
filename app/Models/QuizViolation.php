<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizViolation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'quiz_attempt_id',
        'violation_type',
        'details',
        'points',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    /**
     * Get the user that committed the violation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the quiz where the violation occurred.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the attempt where the violation occurred.
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    /**
     * Get violations count for a user and quiz.
     */
    public static function getViolationCount($userId, $quizId)
    {
        return static::where('user_id', $userId)
                    ->where('quiz_id', $quizId)
                    ->count();
    }

    /**
     * Record a new violation.
     */
    public static function recordViolation($userId, $quizId, $violationType, $details = null, $points = 1, $attemptId = null)
    {
        return static::create([
            'user_id' => $userId,
            'quiz_id' => $quizId,
            'quiz_attempt_id' => $attemptId,
            'violation_type' => $violationType,
            'details' => $details,
            'points' => $points,
            'occurred_at' => now(),
        ]);
    }
}
