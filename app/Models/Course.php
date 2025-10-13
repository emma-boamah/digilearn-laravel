<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Services\UrlObfuscator;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'grade_level',
        'subject',
        'thumbnail_path',
        'price',
        'is_featured',
        'status',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_featured' => 'boolean',
    ];

    /**
     * Get the user who created the course.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the videos associated with this course.
     */
    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class, 'course_videos')
                    ->withPivot('order')
                    ->orderBy('course_videos.order');
    }

    /**
     * Get the documents associated with this course.
     */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'course_documents')
                    ->withPivot('order')
                    ->orderBy('course_documents.order');
    }

    /**
     * Get the quizzes associated with this course.
     */
    public function quizzes(): BelongsToMany
    {
        return $this->belongsToMany(Quiz::class, 'course_quizzes')
                    ->withPivot('order')
                    ->orderBy('course_quizzes.order');
    }

    /**
     * Check if course is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if course is featured
     */
    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    /**
     * Get the course thumbnail URL
     */
    public function getThumbnailUrl(): string
    {
        if ($this->thumbnail_path) {
            return asset('storage/' . $this->thumbnail_path);
        }

        return asset('images/course-placeholder.png'); // Default placeholder
    }

    /**
     * Get formatted price
     */
    public function getFormattedPrice(): string
    {
        if ($this->price > 0) {
            return 'GHS ' . number_format($this->price, 2);
        }

        return 'Free';
    }

    /**
     * Get course statistics
     */
    public function getStats(): array
    {
        return [
            'videos_count' => $this->videos()->count(),
            'documents_count' => $this->documents()->count(),
            'quizzes_count' => $this->quizzes()->count(),
            'total_content' => $this->videos()->count() + $this->documents()->count() + $this->quizzes()->count(),
        ];
    }

    /**
     * Scope for published courses
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for featured courses
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for courses by grade level
     */
    public function scopeByGradeLevel($query, $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    /**
     * Scope for courses by subject
     */
    public function scopeBySubject($query, $subject)
    {
        return $query->where('subject', $subject);
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

        // Find the course by ID
        $course = $this->where('id', $parsed['id'])->first();

        // Optional: Verify slug matches for SEO purposes
        if ($course && $parsed['slug'] !== UrlObfuscator::generateSlug($course->title)) {
            // Slug doesn't match, but we still return the course for flexibility
            // In production, you might want to redirect to the correct URL
        }

        return $course;
    }
}
