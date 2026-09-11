<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Curriculum extends Model
{
    use HasFactory;

    protected $table = 'curricula';

    protected $fillable = [
        'title',
        'education_body',
        'academic_year',
        'subject_id',
        'level_id',
        'level_group_id',
        'file_path',
        'file_size_bytes',
        'extraction_status',
        'is_approved',
        'approved_by',
        'approved_at',
        'extraction_error',
        'raw_extraction_json',
        'uploaded_by',
        'notes',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'file_size_bytes' => 'integer',
        'raw_extraction_json' => 'array',
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

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function strands(): HasMany
    {
        return $this->hasMany(CurriculumStrand::class, 'curriculum_id')->orderBy('sort_order');
    }

    public function subStrands(): HasManyThrough
    {
        return $this->hasManyThrough(CurriculumSubStrand::class, CurriculumStrand::class, 'curriculum_id', 'strand_id');
    }

    public function textbooks(): HasMany
    {
        return $this->hasMany(Textbook::class, 'curriculum_id');
    }

    public function totalIndicatorsCount(): int
    {
        return CurriculumIndicator::whereHas('subStrand.strand', function ($q) {
            $q->where('curriculum_id', $this->id);
        })->count();
    }
}
