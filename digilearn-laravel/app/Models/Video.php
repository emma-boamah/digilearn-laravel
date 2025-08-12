<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'video_path',
        'thumbnail_path',
        'grade_level',
        'duration_seconds',
        'description',
        'is_featured',
        'uploaded_by',
        'uploader_ip',
        'uploader_user_agent',
        'views',
    ];

    /**
     * Get the user who uploaded the video.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the quizzes associated with the video.
     */
    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }
}
