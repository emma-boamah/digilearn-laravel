<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\UrlObfuscator;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subject_id',
        'video_id',
        'grade_level',
        'uploaded_by',
        'quiz_data',
        'views_count',
        'attempts_count',
        'is_featured',
        'time_limit_minutes',
        'average_rating',
        'total_ratings',
        'difficulty_level',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'time_limit_minutes' => 'integer',
        'average_rating' => 'decimal:2',
        'total_ratings' => 'integer',
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

    /**
     * Get the subject associated with the quiz.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Get the ratings for this quiz.
     */
    public function ratings()
    {
        return $this->hasMany(QuizRating::class);
    }

    /**
     * Get the attempts for this quiz.
     */
    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Get formatted time limit
     */
    public function getFormattedTimeLimitAttribute()
    {
        if (!$this->time_limit_minutes) {
            return 'No limit';
        }

        if ($this->time_limit_minutes < 60) {
            return $this->time_limit_minutes . ' min';
        }

        $hours = floor($this->time_limit_minutes / 60);
        $minutes = $this->time_limit_minutes % 60;

        if ($minutes == 0) {
            return $hours . ' hour' . ($hours > 1 ? 's' : '');
        }

        return $hours . 'h ' . $minutes . 'm';
    }

    /**
     * Get total attempts count from all users
     */
    public function getTotalAttemptsCountAttribute()
    {
        return $this->attempts()->count();
    }

    /**
     * Get user's rating for this quiz
     */
    public function getUserRating($userId)
    {
        return $this->ratings()->where('user_id', $userId)->first();
    }

    /**
     * Calculate and update average rating
     */
    public function updateAverageRating()
    {
        $stats = $this->ratings()->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total_ratings')->first();

        $this->update([
            'average_rating' => $stats->avg_rating ?? 0,
            'total_ratings' => $stats->total_ratings ?? 0,
        ]);

        return $this;
    }

    /**
     * Scope for featured quizzes
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for quizzes by grade level
     */
    public function scopeByGradeLevel($query, $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    /**
     * Get the questions from quiz_data
     */
    public function questions()
    {
        $quizData = json_decode($this->quiz_data, true);
        return collect($quizData['questions'] ?? []);
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

        // Find the quiz by ID
        $quiz = $this->where('id', $parsed['id'])->first();

        // Optional: Verify slug matches for SEO purposes
        if ($quiz && $parsed['slug'] !== UrlObfuscator::generateSlug($quiz->title)) {
            // Slug doesn't match, but we still return the quiz for flexibility
            // In production, you might want to redirect to the correct URL
        }

        return $quiz;
    }
}
