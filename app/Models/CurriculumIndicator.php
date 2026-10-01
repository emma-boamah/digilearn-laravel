<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CurriculumIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_strand_id',
        'indicator_code',
        'title',
        'description',
        'exemplars',
        'student_lesson_markdown',
        'student_lesson_html',
        'lesson_source',
        'lesson_key_takeaways',
        'lesson_quiz',
        'lesson_generated_at',
        'sort_order',
    ];

    protected $casts = [
        'lesson_key_takeaways' => 'array',
        'lesson_quiz' => 'array',
        'lesson_generated_at' => 'datetime',
    ];

    /**
     * Get a clean, student-friendly topic title (stripping teacher syllabus jargon).
     */
    public function getStudentTitleAttribute(): string
    {
        $raw = $this->title;
        if (empty($raw)) return 'Lesson Topic';

        // 1. Strip syllabus numbering prefix if present (e.g. "B7.1.1.1.1: ")
        $cleaned = preg_replace('/^(?:(?:Strand|Sub-Strand|Indicator|Content\s*Standard)\s*\d*(?:\.\d+)*\s*[:\-]?\s*|B\d+(?:\.\d+)+\s*[:\-]?\s*)/i', '', $raw);

        // 2. Strip trailing instructional clauses like "using graph sheets...", "and represent the comparison..."
        $cleaned = preg_replace('/\s+(using|with|by using|and represent|recorded as|in terms of|to the nearest)(\s+.*)?$/i', '', $cleaned);
        $cleaned = preg_replace('/\s+(more than|less than|greater than)\s*$/i', '', $cleaned);
        $cleaned = preg_replace('/\s+(and|or|to|for|in|of|by|from|using|with)\s*$/i', '', $cleaned);

        // 3. Humanize common syllabus verbs at the start:
        $cleaned = preg_replace('/^(Demonstrate\s+(an\s+)?understanding\s+of\s+)/i', 'Understanding ', $cleaned);
        $cleaned = preg_replace('/^(Model\s+)/i', 'Understanding ', $cleaned);
        $cleaned = preg_replace('/^(Apply\s+)/i', 'Working with ', $cleaned);
        $cleaned = preg_replace('/^(Perform\s+)/i', 'Practicing ', $cleaned);
        $cleaned = preg_replace('/^(Identify\s+)/i', 'Recognizing ', $cleaned);
        $cleaned = preg_replace('/^(Round\s+(\([^)]+\)\s+)?)/i', 'Rounding ', $cleaned);
        $cleaned = preg_replace('/^(Compare\s+and\s+order\s+)/i', 'Comparing and Ordering ', $cleaned);

        $cleaned = trim($cleaned, " \t\n\r\0\x0B,;:-");

        // If the cleaned title is concise and reasonable, return it
        if (strlen($cleaned) >= 5 && strlen($cleaned) <= 100) {
            return ucfirst($cleaned);
        }

        return $this->title;
    }

    public function subStrand(): BelongsTo
    {
        return $this->belongsTo(CurriculumSubStrand::class, 'sub_strand_id');
    }

    public function textbookSections(): HasMany
    {
        return $this->hasMany(TextbookSection::class, 'curriculum_indicator_id');
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(GuidedLearningProgress::class, 'curriculum_indicator_id');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(CurriculumMedia::class, 'mediable')->orderBy('sort_order');
    }

    /**
     * Check if a specific user has completed this indicator
     */
    public function isCompletedBy(?int $userId): bool
    {
        if (!$userId) return false;

        return $this->progressRecords()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Get user progress status: not_started, in_progress, completed
     */
    public function getStatusForUser(?int $userId): string
    {
        if (!$userId) return 'not_started';

        $progress = $this->progressRecords()
            ->where('user_id', $userId)
            ->first();

        return $progress ? $progress->status : 'not_started';
    }
}
