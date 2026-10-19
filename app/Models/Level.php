<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    /** @var array */
    protected $fillable = [
        'level_group_id',
        'title',
        'slug',
        'description',
        'rank'
    ];

    /**
     * Get the level group that owns the level.
     */
    public function levelGroup()
    {
        return $this->belongsTo(LevelGroup::class);
    }

    /**
     * Get all of the videos assigned to this level.
     */
    public function videos()
    {
        return $this->morphedByMany(Video::class, 'levelable');
    }

    /**
     * Get all of the documents assigned to this level.
     */
    public function documents()
    {
        return $this->morphedByMany(Document::class, 'levelable');
    }

    /**
     * Get all of the quizzes assigned to this level.
     */
    public function quizzes()
    {
        return $this->morphedByMany(Quiz::class, 'levelable');
    }
}
