<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Services\UrlObfuscator;
use Illuminate\Support\Facades\Log;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subject_id',
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
        'comments_count',
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
        return $this->hasOne(Quiz::class);
    }

    /**
     * Get the primary subject for this video.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Get the subjects associated with this video (many-to-many).
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class);
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
        // Debug logging
        Log::info('Video::getVideoUrl called', [
            'video_id' => $this->id,
            'video_source' => $this->video_source,
            'external_video_url' => $this->external_video_url,
            'status' => $this->status,
            'mux_playback_id' => $this->mux_playback_id ?? 'none',
            'vimeo_id' => $this->vimeo_id ?? 'none',
            'video_path' => $this->video_path,
            'temp_file_path' => $this->temp_file_path,
            'is_temp_expired' => $this->isTempExpired(),
            'is_approved' => $this->isApproved()
        ]);

        // Handle external video sources first
        if ($this->video_source !== 'local' && $this->external_video_url) {
            Log::info('Video::getVideoUrl - Using external video URL', [
                'video_id' => $this->id,
                'external_video_url' => $this->external_video_url
            ]);
            return $this->external_video_url;
        }

        // Priority: External sources by video_source > Mux > Vimeo > Local files
        if ($this->isApproved()) {
            // Handle external video sources first
            if ($this->video_source === 'youtube' && $this->external_video_url) {
                Log::info('Video::getVideoUrl - Using YouTube URL', [
                    'video_id' => $this->id,
                    'youtube_url' => $this->external_video_url
                ]);
                return $this->external_video_url;
            }

            if ($this->video_source === 'vimeo' && $this->vimeo_id) {
                $vimeoUrl = "https://player.vimeo.com/video/{$this->vimeo_id}";
                Log::info('Video::getVideoUrl - Using Vimeo URL', [
                    'video_id' => $this->id,
                    'vimeo_url' => $vimeoUrl
                ]);
                return $vimeoUrl;
            }

            if ($this->video_source === 'mux' && $this->mux_playback_id) {
                $muxUrl = "https://stream.mux.com/{$this->mux_playback_id}.m3u8";
                Log::info('Video::getVideoUrl - Using Mux URL', [
                    'video_id' => $this->id,
                    'mux_url' => $muxUrl
                ]);
                return $muxUrl;
            }

            // Fallback for legacy videos without video_source set
            if ($this->mux_playback_id) {
                $muxUrl = "https://stream.mux.com/{$this->mux_playback_id}.m3u8";
                Log::info('Video::getVideoUrl - Using Mux URL (legacy)', [
                    'video_id' => $this->id,
                    'mux_url' => $muxUrl
                ]);
                return $muxUrl;
            }

            if ($this->vimeo_id) {
                $vimeoUrl = "https://player.vimeo.com/video/{$this->vimeo_id}";
                Log::info('Video::getVideoUrl - Using Vimeo URL (legacy)', [
                    'video_id' => $this->id,
                    'vimeo_url' => $vimeoUrl
                ]);
                return $vimeoUrl;
            }
        }

        // Use streaming route for better browser compatibility
        if ($this->temp_file_path && !$this->isTempExpired()) {
            $streamUrl = route('admin.content.videos.stream', $this->id);
            Log::info('Video::getVideoUrl - Using temp file stream URL', [
                'video_id' => $this->id,
                'stream_url' => $streamUrl,
                'temp_file_path' => $this->temp_file_path
            ]);
            return $streamUrl;
        }

        if ($this->video_path) {
            $streamUrl = route('admin.content.videos.stream', $this->id);
            Log::info('Video::getVideoUrl - Using video file stream URL', [
                'video_id' => $this->id,
                'stream_url' => $streamUrl,
                'video_path' => $this->video_path
            ]);
            return $streamUrl;
        }

        Log::warning('Video::getVideoUrl - No valid video URL found', [
            'video_id' => $this->id,
            'all_attributes' => $this->toArray()
        ]);
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
     * Get the thumbnail URL with fallback to video-generated thumbnail
     */
    public function getThumbnailUrl()
    {
        // First, check for custom uploaded thumbnail
        if ($this->thumbnail_path) {
            return asset('storage/' . $this->thumbnail_path);
        }

        // Fallback to video-generated thumbnail based on source
        if ($this->video_source === 'mux' && $this->mux_playback_id) {
            return "https://image.mux.com/{$this->mux_playback_id}/thumbnail.jpg?time=10";
        }

        if ($this->video_source === 'vimeo' && $this->vimeo_id) {
            return $this->getVimeoThumbnailUrl();
        }

        if ($this->video_source === 'youtube' && $this->external_video_id) {
            return $this->getYouTubeThumbnailUrl();
        }

        if ($this->video_source === 'local' || (!$this->video_source && ($this->video_path || $this->temp_file_path))) {
            return $this->getLocalVideoThumbnailUrl();
        }

        // Default placeholder
        return asset('images/video-placeholder.jpg');
    }

    /**
     * Get Vimeo thumbnail URL
     */
    private function getVimeoThumbnailUrl()
    {
        if (!$this->vimeo_id) {
            return asset('images/video-placeholder.jpg');
        }

        try {
            $vimeoService = app(\App\Services\VimeoService::class);
            $videoInfo = $vimeoService->getVideoInfo($this->vimeo_id);

            if ($videoInfo && isset($videoInfo['pictures']['sizes'])) {
                // Get the largest thumbnail
                $sizes = $videoInfo['pictures']['sizes'];
                $largest = end($sizes); // Last one is usually largest
                return $largest['link'] ?? asset('images/video-placeholder.jpg');
            }
        } catch (\Exception $e) {
            Log::warning('Failed to get Vimeo thumbnail', [
                'video_id' => $this->id,
                'vimeo_id' => $this->vimeo_id,
                'error' => $e->getMessage()
            ]);
        }

        return asset('images/video-placeholder.jpg');
    }

    /**
     * Get YouTube thumbnail URL
     */
    private function getYouTubeThumbnailUrl()
    {
        if (!$this->external_video_id) {
            return asset('images/video-placeholder.jpg');
        }

        // Try max resolution first, then fallback to default
        $maxResUrl = "https://img.youtube.com/vi/{$this->external_video_id}/maxresdefault.jpg";

        // For now, just return the maxres URL - in production you might want to check if it exists
        // YouTube thumbnails are publicly accessible without API calls
        return $maxResUrl;
    }

    /**
     * Get local video thumbnail URL (generate if needed)
     */
    private function getLocalVideoThumbnailUrl()
    {
        $videoPath = $this->video_path ?: $this->temp_file_path;

        if (!$videoPath) {
            return asset('images/video-placeholder.jpg');
        }

        // Check if thumbnail already exists
        $thumbnailPath = 'thumbnails/' . md5($videoPath) . '.jpg';
        $fullThumbnailPath = storage_path('app/public/' . $thumbnailPath);

        if (file_exists($fullThumbnailPath)) {
            return asset('storage/' . $thumbnailPath);
        }

        // Generate thumbnail
        try {
            $fullVideoPath = storage_path('app/public/' . $videoPath);

            if (!file_exists($fullVideoPath)) {
                return asset('images/video-placeholder.jpg');
            }

            // Ensure thumbnails directory exists
            $thumbnailDir = dirname($fullThumbnailPath);
            if (!is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            // Use FFmpeg to generate thumbnail at 10 seconds
            $command = "ffmpeg -i " . escapeshellarg($fullVideoPath) . " -ss 00:00:10 -vframes 1 -q:v 2 " . escapeshellarg($fullThumbnailPath) . " 2>&1";

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($fullThumbnailPath)) {
                return asset('storage/' . $thumbnailPath);
            } else {
                Log::warning('FFmpeg thumbnail generation failed', [
                    'video_id' => $this->id,
                    'video_path' => $videoPath,
                    'command' => $command,
                    'output' => implode("\n", $output),
                    'return_code' => $returnCode
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to generate local video thumbnail', [
                'video_id' => $this->id,
                'video_path' => $videoPath,
                'error' => $e->getMessage()
            ]);
        }

        return asset('images/video-placeholder.jpg');
    }

    /**
     * Get video duration in seconds from various sources
     */
    public function getDuration()
    {
        // If duration is already stored, return it
        if ($this->duration_seconds) {
            return $this->duration_seconds;
        }

        // Fetch duration based on source
        switch ($this->video_source) {
            case 'vimeo':
                return $this->getVimeoDuration();

            case 'youtube':
                return $this->getYouTubeDuration();

            case 'mux':
                return $this->getMuxDuration();

            case 'local':
            default:
                // For local videos, duration should be stored in duration_seconds
                // If not, return null
                return null;
        }
    }

    /**
     * Get Vimeo video duration
     */
    private function getVimeoDuration()
    {
        if (!$this->vimeo_id) {
            return null;
        }

        try {
            $vimeoService = app(\App\Services\VimeoService::class);
            $videoInfo = $vimeoService->getVideoInfo($this->vimeo_id);

            if ($videoInfo && isset($videoInfo['duration'])) {
                $duration = (int) $videoInfo['duration'];
                // Cache the duration
                $this->update(['duration_seconds' => $duration]);
                return $duration;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to get Vimeo duration', [
                'video_id' => $this->id,
                'vimeo_id' => $this->vimeo_id,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Get YouTube video duration
     */
    private function getYouTubeDuration()
    {
        if (!$this->external_video_id) {
            return null;
        }

        try {
            $youtubeService = app(\App\Services\YouTubeService::class);
            $duration = $youtubeService->getVideoDuration($this->external_video_id);

            if ($duration) {
                // Cache the duration
                $this->update(['duration_seconds' => $duration]);
                return $duration;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to get YouTube duration', [
                'video_id' => $this->id,
                'youtube_id' => $this->external_video_id,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Get Mux video duration
     */
    private function getMuxDuration()
    {
        if (!$this->mux_asset_id) {
            return null;
        }

        try {
            $muxService = app(\App\Services\MuxService::class);
            $assetInfo = $muxService->getAsset($this->mux_asset_id);

            if ($assetInfo['success'] && isset($assetInfo['duration'])) {
                $duration = (float) $assetInfo['duration'];
                // Cache the duration
                $this->update(['duration_seconds' => $duration]);
                return $duration;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to get Mux duration', [
                'video_id' => $this->id,
                'mux_asset_id' => $this->mux_asset_id,
                'error' => $e->getMessage()
            ]);
        }

        return null;
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
