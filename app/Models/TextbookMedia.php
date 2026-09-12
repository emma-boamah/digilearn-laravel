<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TextbookMedia extends Model
{
    use HasFactory;

    protected $table = 'textbook_media';

    protected $fillable = [
        'section_id',
        'file_path',
        'caption',
        'page_number',
        'sort_order',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(TextbookSection::class, 'section_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
