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
        'lesson_level_group',
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
        // Ensure we have both individual level and level group
        $individualLevel = $lessonData['level']; // e.g., "Primary 1"
        $levelGroup = $lessonData['level_group'] ?? static::getLevelGroup($individualLevel); // e.g., "primary-lower"

        $completion = static::updateOrCreate(
            [
                'user_id' => $userId,
                'lesson_id' => $lessonData['id'],
            ],
            [
                'lesson_title' => $lessonData['title'],
                'lesson_subject' => $lessonData['subject'],
                'lesson_level' => $individualLevel, // Store individual level
                'lesson_level_group' => $levelGroup, // Store level group for aggregation
                'total_duration_seconds' => $totalDurationSeconds,
                'first_watched_at' => now(),
                'last_watched_at' => now(),
            ]
        );

        // Accumulate watch time from all sessions
        $previousWatchTime = $completion->watch_time_seconds ?? 0;
        $completion->watch_time_seconds = $previousWatchTime + $watchTimeSeconds;
        $completion->completion_percentage = min(100, ($completion->watch_time_seconds / $totalDurationSeconds) * 100);

        // Get the watch threshold from standards
        $standards = \App\Models\ProgressionStandard::getStandardsForLevel($levelGroup);
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
     * Get level group from individual level
     */
    private static function getLevelGroup($individualLevel)
    {
        $groups = [
            'Primary 1' => 'primary-lower',
            'Primary 2' => 'primary-lower',
            'Primary 3' => 'primary-lower',
            'Primary 4' => 'primary-upper',
            'Primary 5' => 'primary-upper',
            'Primary 6' => 'primary-upper',
            'JHS 1' => 'jhs',
            'JHS 2' => 'jhs',
            'JHS 3' => 'jhs',
            'SHS 1' => 'shs',
            'SHS 2' => 'shs',
            'SHS 3' => 'shs',
            'Tertiary' => 'tertiary',
        ];

        return $groups[$individualLevel] ?? $individualLevel;
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

    /**
     * Get completion statistics for a user and level group
     */
    public static function getLevelGroupStats($userId, $levelGroup)
    {
        return static::where('user_id', $userId)
                    ->where('lesson_level_group', $levelGroup)
                    ->selectRaw('
                        COUNT(*) as total_lessons,
                        SUM(CASE WHEN fully_completed = 1 THEN 1 ELSE 0 END) as completed_lessons,
                        AVG(completion_percentage) as avg_completion_percentage,
                        SUM(watch_time_seconds) as total_watch_time
                    ')
                    ->first();
    }
}
