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
        'document_path',
        'quiz_id'
    ];

    /**
     * Get the user who uploaded the video.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Document relationship (if using document_id)
    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    /**
     * Get the quizzes associated with the video.
     */
    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    // Get single quiz for a video
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }
}
