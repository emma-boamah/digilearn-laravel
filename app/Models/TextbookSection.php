<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TextbookSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'title',
        'content_html',
        'content_markdown',
        'sort_order',
        'page_start',
        'page_end',
        'curriculum_indicator_id',
    ];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(TextbookChapter::class, 'chapter_id');
    }

    public function curriculumIndicator(): BelongsTo
    {
        return $this->belongsTo(CurriculumIndicator::class, 'curriculum_indicator_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(TextbookMedia::class, 'section_id')->orderBy('sort_order');
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(GuidedLearningProgress::class, 'textbook_section_id');
    }
}
