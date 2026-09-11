<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TextbookChapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'textbook_id',
        'title',
        'page_start',
        'page_end',
        'sort_order',
        'curriculum_strand_id',
    ];

    public function textbook(): BelongsTo
    {
        return $this->belongsTo(Textbook::class);
    }

    public function curriculumStrand(): BelongsTo
    {
        return $this->belongsTo(CurriculumStrand::class, 'curriculum_strand_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(TextbookSection::class, 'chapter_id')->orderBy('sort_order');
    }
}
