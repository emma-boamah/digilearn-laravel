<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'file_path',
        'grade_level',
        'description',
        'uploaded_by',
        'video_id',
        'is_featured',
        'views',
    ];

    /**
     * Get the user who uploaded the document.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the video this document is related to.
     */
    public function video()
    {
        return $this->belongsTo(Video::class, 'video_id');
    }
}
