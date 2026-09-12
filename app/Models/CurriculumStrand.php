<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CurriculumStrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'curriculum_id',
        'title',
        'description',
        'sort_order',
        'grade_label',
        'level_id',
    ];

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function subStrands(): HasMany
    {
        return $this->hasMany(CurriculumSubStrand::class, 'strand_id')->orderBy('sort_order');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(CurriculumMedia::class, 'mediable')->orderBy('sort_order');
    }
}
