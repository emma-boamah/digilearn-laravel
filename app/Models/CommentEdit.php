<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentEdit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'comment_id',
        'user_id',
        'old_content',
        'new_content',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the comment that was edited.
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * Get the user who made the edit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
