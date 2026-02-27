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
        $levelGroup = $lessonData['level_group'] ?? \App\Models\ProgressionStandard::getLevelGroup($individualLevel); // e.g., "primary-lower"

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

        // Secure Progress Logic
        // We revert to cumulative tracking based on actual watch time reported by frontend.
        // Frontend only sends watch_time ticks when video is actually playing.
        // If user skips, watch_time is small, so progress won't jump.
        
        $previousWatchTime = $completion->watch_time_seconds ?? 0;
        
        // Add the incremental watch time from this session
        $completion->watch_time_seconds = $previousWatchTime + $watchTimeSeconds;

        // Ensure we don't exceed total duration
        $completion->watch_time_seconds = min($completion->watch_time_seconds, $totalDurationSeconds);
        
        $completion->completion_percentage = $totalDurationSeconds > 0 
            ? min(100, ($completion->watch_time_seconds / $totalDurationSeconds) * 100) 
            : 0;

        // Get the watch threshold from standards
        $standards = \App\Models\ProgressionStandard::getStandardsForLevel($levelGroup);
        $watchThreshold = $standards['lesson_watch_threshold_percentage'];

        // Mark as fully completed if watched above threshold or more
        if ($completion->completion_percentage >= $watchThreshold && !$completion->fully_completed) {
            $completion->fully_completed = true;
            $completion->completed_at = now();
        }

        // Increment times watched only if starting from the beginning (new session)
        // We check if previous watch time was effectively zero to determine if this is a new start
        if ($previousWatchTime < 30) { 
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
    /**
     * Get the watch time formatted (e.g., 1h 20m or 45m).
     */
    public function getFormattedWatchTime()
    {
        $seconds = $this->watch_time_seconds;
        if (!$seconds) return '0s';
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        
        if ($h > 0) return "{$h}h " . ($m > 0 ? "{$m}m" : "");
        if ($m > 0) return "{$m}m " . ($s > 0 ? "{$s}s" : "");
        return "{$s}s";
    }
}
