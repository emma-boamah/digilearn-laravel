<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'lesson_title',
        'lesson_subject',
        'lesson_instructor',
        'lesson_year',
        'lesson_duration',
        'lesson_thumbnail',
        'lesson_video_url',
        'selected_level',
        'saved_at',
    ];

    protected $casts = [
        'saved_at' => 'datetime',
    ];

    /**
     * Get the user that saved this lesson
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a lesson is saved by a user
     */
    public static function isSaved($userId, $lessonId): bool
    {
        return self::where('user_id', $userId)
                   ->where('lesson_id', $lessonId)
                   ->exists();
    }

    /**
     * Get saved lessons for a user
     */
    public static function getSavedLessons($userId)
    {
        return self::where('user_id', $userId)
                   ->orderBy('saved_at', 'desc')
                   ->get();
    }
}
