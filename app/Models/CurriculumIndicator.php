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
        'interactive_segments',
        'lesson_generated_at',
        'sort_order',
    ];

    protected $casts = [
        'lesson_key_takeaways' => 'array',
        'lesson_quiz' => 'array',
        'interactive_segments' => 'array',
        'lesson_generated_at' => 'datetime',
    ];

    /**
     * Get a clean, student-friendly topic title (stripping teacher syllabus jargon).
     */
    public function getStudentTitleAttribute(): string
    {
        $code = trim($this->indicator_code ?? '');

        // 1. Canonical topic dictionary for known NaCCA curriculum indicators
        $canonicalMap = [
            'B7.2.1.1.1' => 'Extending Patterns and Relations',
            'B7.2.1.1.2' => 'Describing Rules for Relations',
            'B7.2.1.1.3' => 'Identifying Rules in Patterns and Mappings',
            'B7.2.1.1.4' => 'Table of Values and Graphing Relations',
            'B7.1.1.1.1' => 'Modeling Quantities Over 1,000,000,000',
            'B7.1.1.1.2' => 'Comparing and Ordering Large Numbers',
            'B7.1.1.1.3' => 'Rounding Large Numbers',
            'B7.1.1.1.4' => 'Rounding Decimals',
            'B7.1.2.3.1' => 'Repeated Factors and Powers of Numbers',
            'B7.1.2.3.2' => 'Laws of Indices (Multiplication & Division)',
            'B7.1.2.3.3' => 'The Power of Zero (a⁰ = 1)',
            'B7.1.2.3.4' => 'Negative and Fractional Exponents',
            'B7.1.2.3.5' => 'Highest Common Factor using Prime Factors',
            'B7.3.2.1.2' => 'Relationships Between Diameter and Circumference',
            'B8.1.4.1.4' => 'Recognizing Proportional Relationships',
            'B8.2.1.1.1' => 'Gradient and Linear Equations (y = mx + c)',
            'B8.2.1.1.2' => 'Using Graphs of Linear Relations to Find Missing Elements',
            'B8.2.1.1.3' => 'Real-Life Applications of Linear Relations',
            'B8.4.1.1.1' => 'Types of Data: Numerical, Categorical, Grouped & Ungrouped',
            'B9.1.1.2.1' => 'Relationships Between Number Sets',
            'B9.2.1.1.1' => 'Table of Values and Graphs for Simultaneous Relations',
            'B7.3.1.1.1' => 'Measuring and Classifying Angles: Right, Acute, Obtuse and Reflex',
            'B7.3.1.1.2' => 'Complementary and Supplementary Angles',
            'B7.3.1.1.3' => 'Adjacent, Supplementary and Vertically Opposite Angles',
        ];

        if (!empty($code) && isset($canonicalMap[$code])) {
            return $canonicalMap[$code];
        }

        $raw = $this->title;
        if (empty($raw)) return 'Lesson Topic';

        // 2. Strip syllabus numbering prefix if present (e.g. "B7.1.1.1.1: ")
        $cleaned = preg_replace('/^(?:(?:Strand|Sub-Strand|Indicator|Content\s*Standard)\s*\d*(?:\.\d+)*\s*[:\-]?\s*|B\d+(?:\.\d+)+\s*[:\-]?\s*)/i', '', $raw);

        // 3. Strip explicit pedagogical apparatus clauses only (never strip legitimate math clauses)
        $cleaned = preg_replace('/\s+(?:using|with)\s+(?:graph sheets|isometric papers|multi-base blocks|counting objects|bottle tops|bundle sticks|cut-outs|rulers|protractors|paper folding|geoboards|concrete materials|symbolic materials)\b.*$/i', '', $cleaned);
        $cleaned = preg_replace('/\s+to the nearest\s+(?:hundred-thousand|ten-thousands|thousands|hundreds|tens|tenth|hundredth|thousandths?)(?:\s*,?\s*etc\.?)?$/i', '', $cleaned);

        // 4. Humanize common syllabus verbs at the start:
        $cleaned = preg_replace('/^(Demonstrate\s+(an\s+)?understanding\s+of\s+)/i', 'Understanding ', $cleaned);
        $cleaned = preg_replace('/^(Extend\s+a\s+given\s+relation\b.*)/i', 'Extending Patterns and Relations', $cleaned);
        $cleaned = preg_replace('/^(Model\s+)/i', 'Modeling ', $cleaned);
        $cleaned = preg_replace('/^(Apply\s+)/i', 'Working with ', $cleaned);
        $cleaned = preg_replace('/^(Perform\s+)/i', 'Practicing ', $cleaned);
        $cleaned = preg_replace('/^(Identify\s+)/i', 'Recognizing ', $cleaned);
        $cleaned = preg_replace('/^(Round\s+(\([^)]+\)\s+)?)/i', 'Rounding ', $cleaned);
        $cleaned = preg_replace('/^(Compare\s+and\s+order\s+)/i', 'Comparing and Ordering ', $cleaned);

        // 5. Clean trailing punctuation, trailing conjunctions, and dangling prepositions
        $cleaned = preg_replace('/\s+(?:presented|given|the|a|an|in|on|at|by|with|for|to|from|of|and|or|between)\s*$/i', '', $cleaned);
        $cleaned = trim($cleaned, " \t\n\r\0\x0B,;:-.");

        // 6. If the title was cut too short (e.g. "Illustrate" or "Recognise"), combine with Sub-Strand name
        if (strlen($cleaned) < 14) {
            $subStrandTitle = $this->subStrand?->title ?? '';
            $cleanSub = trim(preg_replace('/^SUB-STRAND\s*\d*[:\-]?\s*/i', '', $subStrandTitle));
            if (!empty($cleanSub)) {
                return ucwords(strtolower($cleanSub)) . (strlen($cleaned) > 2 ? ': ' . ucfirst($cleaned) : '');
            }
        }

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
