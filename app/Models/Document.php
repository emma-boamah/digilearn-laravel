<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\UrlObfuscator;

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
        'file_type',
        'file_size_bytes',
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

        // Find the document by ID
        $document = $this->where('id', $parsed['id'])->first();

        // Optional: Verify slug matches for SEO purposes
        if ($document && $parsed['slug'] !== UrlObfuscator::generateSlug($document->title)) {
            // Slug doesn't match, but we still return the document for flexibility
            // In production, you might want to redirect to the correct URL
        }

        return $document;
    }
}
