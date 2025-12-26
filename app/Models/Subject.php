<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the videos that belong to this subject (many-to-many).
     */
    public function videos()
    {
        return $this->belongsToMany(Video::class);
    }

    /**
     * Get the primary videos for this subject (one-to-many).
     */
    public function primaryVideos()
    {
        return $this->hasMany(Video::class, 'subject_id');
    }

    /**
     * Get the quizzes associated with this subject.
     */
    public function quizzes()
    {
        return $this->hasMany(\App\Models\Quiz::class, 'subject_id');
    }
}
