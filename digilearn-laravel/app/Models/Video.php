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
        'temp_file_path',
        'thumbnail_path',
        'grade_level',
        'duration_seconds',
        'file_size_bytes',
        'description',
        'is_featured',
        'status',
        'vimeo_id',
        'vimeo_embed_url',
        'uploaded_by',
        'reviewed_by',
        'review_notes',
        'uploader_ip',
        'uploader_user_agent',
        'views',
        'document_path',
        'quiz_id'
    ];

    protected $casts = [
        'temp_expires_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    /**
     * Get the user who uploaded the video.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the user who reviewed the video.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
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

    /**
     * Check if video is pending review
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if video is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if video is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if video is being processed (uploading to Vimeo)
     */
    public function isProcessing()
    {
        return $this->status === 'processing';
    }

    /**
     * Check if temporary file has expired
     */
    public function isTempExpired()
    {
        return $this->temp_expires_at && $this->temp_expires_at->isPast();
    }

    /**
     * Get the video URL for viewing
     */
    public function getVideoUrl()
    {
        if ($this->isApproved() && $this->vimeo_id) {
            return "https://player.vimeo.com/video/{$this->vimeo_id}";
        }
        
        if ($this->temp_file_path && !$this->isTempExpired()) {
            return asset('storage/' . $this->temp_file_path);
        }
        
        return $this->video_path ? asset('storage/' . $this->video_path) : null;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSize()
    {
        if (!$this->file_size_bytes) return 'Unknown';
        
        $bytes = $this->file_size_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Scope for pending videos
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved videos
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
