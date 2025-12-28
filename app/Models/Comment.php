<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CommentUserLike;

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
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Increment comments count when comment is created
        static::created(function ($comment) {
            if ($comment->video_id) {
                $comment->video->increment('comments_count');
            }
        });

        // Decrement comments count when comment is deleted
        static::deleted(function ($comment) {
            if ($comment->video_id) {
                $comment->video->decrement('comments_count');
            }
        });
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
     * Get the user likes/dislikes for this comment.
     */
    public function userLikes(): HasMany
    {
        return $this->hasMany(CommentUserLike::class);
    }

    /**
     * Check if a user has liked this comment.
     */
    public function isLikedBy($userId): bool
    {
        return $this->userLikes()->where('user_id', $userId)->where('type', 'like')->exists();
    }

    /**
     * Check if a user has disliked this comment.
     */
    public function isDislikedBy($userId): bool
    {
        return $this->userLikes()->where('user_id', $userId)->where('type', 'dislike')->exists();
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
