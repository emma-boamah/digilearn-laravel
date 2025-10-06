<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'lesson_title',
        'lesson_subject',
        'lesson_level',
        'watch_time_seconds',
        'total_duration_seconds',
        'completion_percentage',
        'fully_completed',
        'times_watched',
        'first_watched_at',
        'last_watched_at',
        'completed_at',
        'watch_sessions',
    ];

    protected $casts = [
        'fully_completed' => 'boolean',
        'first_watched_at' => 'datetime',
        'last_watched_at' => 'datetime',
        'completed_at' => 'datetime',
        'watch_sessions' => 'array',
        'completion_percentage' => 'decimal:2',
    ];

    /**
     * Get the user that owns the completion.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record lesson watch progress
     */
    public static function recordWatchProgress($userId, $lessonData, $watchTimeSeconds, $totalDurationSeconds)
    {
        $completion = static::updateOrCreate(
            [
                'user_id' => $userId,
                'lesson_id' => $lessonData['id'],
            ],
            [
                'lesson_title' => $lessonData['title'],
                'lesson_subject' => $lessonData['subject'],
                'lesson_level' => $lessonData['level'],
                'total_duration_seconds' => $totalDurationSeconds,
                'last_watched_at' => now(),
            ]
        );

        // Update watch time and completion percentage
        $completion->watch_time_seconds = max($completion->watch_time_seconds, $watchTimeSeconds);
        $completion->completion_percentage = min(100, ($completion->watch_time_seconds / $totalDurationSeconds) * 100);
        
        // Get the watch threshold from standards
        $standards = \App\Models\ProgressionStandard::getStandardsForLevel($lessonData['level_group'] ?? 'primary-lower');
        $watchThreshold = $standards['lesson_watch_threshold_percentage'];

        // Mark as fully completed if watched above threshold or more
        if ($completion->completion_percentage >= $watchThreshold && !$completion->fully_completed) {
            $completion->fully_completed = true;
            $completion->completed_at = now();
        }

        // Increment times watched if starting over
        if ($watchTimeSeconds < 30) { // If watching from beginning
            $completion->times_watched++;
        }

        // Set first watched time if not set
        if (!$completion->first_watched_at) {
            $completion->first_watched_at = now();
        }

        $completion->save();

        return $completion;
    }

    /**
     * Get completion statistics for a user and level
     */
    public static function getLevelStats($userId, $level)
    {
        return static::where('user_id', $userId)
                    ->where('lesson_level', $level)
                    ->selectRaw('
                        COUNT(*) as total_lessons,
                        SUM(CASE WHEN fully_completed = 1 THEN 1 ELSE 0 END) as completed_lessons,
                        AVG(completion_percentage) as avg_completion_percentage,
                        SUM(watch_time_seconds) as total_watch_time
                    ')
                    ->first();
    }
}
