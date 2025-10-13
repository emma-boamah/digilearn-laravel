<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'video_id',
        'parent_id',
        'likes_count',
        'dislikes_count',
        'is_approved',
    ];

    protected $casts = [
        'likes_count' => 'integer',
        'dislikes_count' => 'integer',
        'is_approved' => 'boolean',
    ];

    /**
     * Get the user who made this comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the video this comment belongs to.
     */
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    /**
     * Get the parent comment (for nested comments).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->with(['user', 'replies']);
    }

    /**
     * Get all comments for a video (including nested replies).
     */
    public function scopeForVideo($query, $videoId)
    {
        return $query->where('video_id', $videoId)
                    ->whereNull('parent_id')
                    ->with(['user', 'replies'])
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get approved comments only.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Get formatted time ago.
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get user's avatar initial.
     */
    public function getAvatarInitialAttribute()
    {
        return strtoupper(substr($this->user->name, 0, 1));
    }
}
