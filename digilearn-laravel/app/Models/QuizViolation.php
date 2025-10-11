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
        'violation_type',
        'details',
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
    public static function recordViolation($userId, $quizId, $violationType, $details = null)
    {
        return static::create([
            'user_id' => $userId,
            'quiz_id' => $quizId,
            'violation_type' => $violationType,
            'details' => $details,
            'occurred_at' => now(),
        ]);
    }
}
