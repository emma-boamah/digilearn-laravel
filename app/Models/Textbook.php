<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'content_extraction_status',
        'is_toc_approved',
        'is_content_approved',
        'raw_toc_json',
        'uploaded_by',
    ];

    protected $casts = [
        'is_toc_approved' => 'boolean',
        'is_content_approved' => 'boolean',
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
}
