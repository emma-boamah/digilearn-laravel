<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class CurriculumMedia extends Model
{
    use HasFactory;

    protected $table = 'curriculum_media';

    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'file_path',
        'caption',
        'page_number',
        'sort_order',
    ];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
