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
}
