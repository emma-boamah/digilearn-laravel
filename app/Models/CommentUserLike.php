<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentUserLike extends Model
{
    protected $fillable = [
        'user_id',
        'comment_id',
        'type',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    /**
     * Get the user who liked/disliked the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comment that was liked/disliked.
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}
