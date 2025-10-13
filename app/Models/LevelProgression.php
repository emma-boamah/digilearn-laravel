<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LevelProgression extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_level',
        'to_level',
        'from_level_group',
        'to_level_group',
        'final_score',
        'lessons_completed',
        'quizzes_passed',
        'average_quiz_score',
        'progression_criteria',
        'auto_progressed',
        'progressed_at',
    ];

    protected $casts = [
        'auto_progressed' => 'boolean',
        'progressed_at' => 'datetime',
        'progression_criteria' => 'array',
        'final_score' => 'decimal:2',
        'average_quiz_score' => 'decimal:2',
    ];

    /**
     * Get the user that owns the progression.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record level progression
     */
    public static function recordProgression($userId, $fromLevel, $toLevel, $progressData)
    {
        return static::create([
            'user_id' => $userId,
            'from_level' => $fromLevel,
            'to_level' => $toLevel,
            'from_level_group' => $progressData['from_level_group'],
            'to_level_group' => $progressData['to_level_group'],
            'final_score' => $progressData['final_score'],
            'lessons_completed' => $progressData['lessons_completed'],
            'quizzes_passed' => $progressData['quizzes_passed'],
            'average_quiz_score' => $progressData['average_quiz_score'],
            'progression_criteria' => $progressData['criteria_met'],
            'auto_progressed' => true,
            'progressed_at' => now(),
        ]);
    }

    /**
     * Get user's progression history
     */
    public static function getUserHistory($userId)
    {
        return static::where('user_id', $userId)
                    ->orderBy('progressed_at', 'desc')
                    ->get();
    }
}
