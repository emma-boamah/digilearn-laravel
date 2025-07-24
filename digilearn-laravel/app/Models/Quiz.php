<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subject',
        'video_id',
        'grade_level',
        'uploaded_by',
        'quiz_data',
        'views_count',
        'attempts_count',
        'is_featured',
    ];

    /**
     * Get the user who uploaded the quiz.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the video associated with the quiz.
     */
    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
