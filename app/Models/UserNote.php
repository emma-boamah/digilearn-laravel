<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'video_id',
        'title',
        'content',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the video that the note belongs to.
     */
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'video_id');
    }

    /**
     * Scope to get notes for a specific user and video.
     */
    public function scopeForUserAndVideo($query, $userId, $videoId)
    {
        return $query->where('user_id', $userId)->where('video_id', $videoId);
    }

    /**
     * Get the formatted created date.
     */
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('M d, Y \a\t g:i A');
    }

    /**
     * Get the formatted updated date.
     */
    public function getFormattedUpdatedAtAttribute()
    {
        return $this->updated_at->format('M d, Y \a\t g:i A');
    }
}