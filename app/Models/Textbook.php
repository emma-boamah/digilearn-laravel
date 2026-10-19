<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Textbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'publisher',
        'isbn',
        'edition',
        'subject_id',
        'level_id',
        'level_group_id',
        'curriculum_id',
        'file_path',
        'file_size_bytes',
        'total_pages',
        'toc_extraction_status',
        'extraction_error',
        'content_extraction_status',
        'is_toc_approved',
        'is_content_approved',
        'is_course_active',
        'raw_toc_json',
        'uploaded_by',
        'extraction_method',
        'extraction_quality',
        'requires_review',
        'ocr_required',
        'local_parser_version',
    ];

    protected $casts = [
        'is_toc_approved' => 'boolean',
        'is_content_approved' => 'boolean',
        'is_course_active' => 'boolean',
        'requires_review' => 'boolean',
        'ocr_required' => 'boolean',
        'file_size_bytes' => 'integer',
        'total_pages' => 'integer',
        'raw_toc_json' => 'array',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function levelGroup(): BelongsTo
    {
        return $this->belongsTo(LevelGroup::class);
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(TextbookChapter::class, 'textbook_id')->orderBy('sort_order');
    }

    public function sections(): HasManyThrough
    {
        return $this->hasManyThrough(
            TextbookSection::class,
            TextbookChapter::class,
            'textbook_id',
            'chapter_id',
            'id',
            'id'
        );
    }

    /**
     * Check whether this textbook course has content in all sections and is published.
     */
    public function isCourseComplete(): bool
    {
        $totalSections = $this->sections()->count();
        if ($totalSections === 0) {
            return false;
        }

        $readySections = $this->sections()
            ->where('is_published', true)
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('content_markdown')->where('content_markdown', '!=', '');
                })->orWhere(function ($sub) {
                    $sub->whereNotNull('content_html')->where('content_html', '!=', '');
                });
            })
            ->count();

        return $readySections === $totalSections;
    }
}
