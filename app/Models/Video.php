<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Services\UrlObfuscator;

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
        'quiz_id',
        'video_source',
        'external_video_id',
        'external_video_url'
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
     * Get the documents associated with the video.
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'video_id');
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
     * Get the comments for this video.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class)->approved()->orderBy('created_at', 'desc');
    }

    /**
     * Get the top-level comments for this video (excluding replies).
     */
    public function topLevelComments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id')->approved()->with(['user', 'replies'])->orderBy('created_at', 'desc');
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
        // Handle external video sources first
        if ($this->video_source !== 'local' && $this->external_video_url) {
            return $this->external_video_url;
        }

        // Priority: Mux > Vimeo > Local files
        if ($this->isApproved()) {
            if ($this->mux_playback_id) {
                return "https://stream.mux.com/{$this->mux_playback_id}.m3u8";
            }

            if ($this->vimeo_id) {
                return "https://player.vimeo.com/video/{$this->vimeo_id}";
            }
        }

        // Use streaming route for better browser compatibility
        if ($this->temp_file_path && !$this->isTempExpired()) {
            return route('admin.content.videos.stream', $this);
        }

        if ($this->video_path) {
            return route('admin.content.videos.stream', $this);
        }

        return null;
    }

    /**
     * Get Mux video URL
     */
    public function getMuxUrl()
    {
        if ($this->mux_playback_id) {
            return "https://stream.mux.com/{$this->mux_playback_id}.m3u8";
        }
        return null;
    }

    /**
     * Get Mux MP4 download URL
     */
    public function getMuxMp4Url()
    {
        if ($this->mux_playback_id) {
            return "https://stream.mux.com/{$this->mux_playback_id}/low.mp4";
        }
        return null;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSize()
    {
        if (!$this->file_size_bytes) return 'Unknown';

        $bytes = $this->file_size_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes >= 1024 && $i < 3; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Safely delete all associated files
     */
    public function deleteFiles()
    {
        $disk = Storage::disk('public');

        // Delete main video file
        if ($this->video_path && $disk->exists($this->video_path)) {
            $disk->delete($this->video_path);
        }

        // Delete temporary video file
        if ($this->temp_file_path && $disk->exists($this->temp_file_path)) {
            $disk->delete($this->temp_file_path);
        }

        // Delete thumbnail
        if ($this->thumbnail_path && $disk->exists($this->thumbnail_path)) {
            $disk->delete($this->thumbnail_path);
        }

        // Delete document
        if ($this->document_path && $disk->exists($this->document_path)) {
            $disk->delete($this->document_path);
        }
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

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'seo_url';
    }

    /**
     * Get the SEO-friendly URL attribute.
     */
    public function getSeoUrlAttribute()
    {
        return UrlObfuscator::createSeoUrl($this->id, $this->title);
    }

    /**
     * Get the slug for SEO URLs.
     */
    public function getSlugAttribute()
    {
        return UrlObfuscator::generateSlug($this->title);
    }

    /**
     * Resolve route binding using SEO URL (encrypted_id-slug).
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $parsed = UrlObfuscator::parseSeoUrl($value);

        if (!$parsed) {
            return null;
        }

        // Find the video by ID
        $video = $this->where('id', $parsed['id'])->first();

        // Optional: Verify slug matches for SEO purposes
        if ($video && $parsed['slug'] !== UrlObfuscator::generateSlug($video->title)) {
            // Slug doesn't match, but we still return the video for flexibility
            // In production, you might want to redirect to the correct URL
        }

        return $video;
    }

    /**
     * Get embed HTML for the video
     */
    public function getEmbedHtml()
    {
        return \App\Services\VideoSourceService::getEmbedHtml($this);
    }

    /**
     * Get canonical URL for the video
     */
    public function getCanonicalUrl()
    {
        return \App\Services\VideoSourceService::getCanonicalUrl($this);
    }

    /**
     * Check if video is from external source
     */
    public function isExternalSource()
    {
        return $this->video_source !== 'local';
    }

    /**
     * Set video source from URL
     */
    public function setVideoSourceFromUrl($url)
    {
        $parsed = \App\Services\VideoSourceService::parseVideoUrl($url);

        if ($parsed) {
            $this->video_source = $parsed['source'];
            $this->external_video_id = $parsed['video_id'];
            $this->external_video_url = $parsed['embed_url'];
            return true;
        }

        return false;
    }
}
