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
        'sort_order',
    ];

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
