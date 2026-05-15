<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'query',
        'grade_level',
        'level_group',
        'ip_address',
        'user_agent',
        'topic',
        'subject',
        'video_id',
        'quiz_id',
        'youtube_video_id',
        'status',
        'gemini_response',
        'youtube_results',
        'error_message',
        'processing_time_ms',
        'roadmap_data',
    ];

    protected $casts = [
        'gemini_response' => 'array',
        'youtube_results' => 'array',
        'roadmap_data' => 'array',
        'processing_time_ms' => 'integer',
    ];

    /**
     * Get the user who made this request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the video that was created/found for this request.
     */
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    /**
     * Scope for successful requests.
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['found_existing', 'created']);
    }

    /**
     * Scope for failed requests.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Check how many requests a user has made today.
     */
    public static function todayCountForUser(int $userId): int
    {
        return static::where('user_id', $userId)
            ->whereDate('created_at', now()->toDateString())
            ->count();
    }

    /**
     * Find an existing successful request for the same topic + grade level.
     */
    public static function findExistingMatch(string $topic, string $gradeLevel): ?self
    {
        return static::where('topic', $topic)
            ->where('grade_level', $gradeLevel)
            ->whereIn('status', ['found_existing', 'created'])
            ->whereNotNull('video_id')
            ->latest()
            ->first();
    }
}
