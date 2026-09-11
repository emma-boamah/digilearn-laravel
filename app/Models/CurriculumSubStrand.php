<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CurriculumSubStrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'strand_id',
        'title',
        'description',
        'content_standard',
        'sort_order',
    ];

    public function strand(): BelongsTo
    {
        return $this->belongsTo(CurriculumStrand::class, 'strand_id');
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(CurriculumIndicator::class, 'sub_strand_id')->orderBy('sort_order');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(CurriculumMedia::class, 'mediable')->orderBy('sort_order');
    }
}
