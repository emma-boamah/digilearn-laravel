<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HeroBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'media_type',
        'media_path',
        'badge_text',
        'cta_text',
        'cta_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Scope query to active banners ordered by sort_order.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc');
    }

    /**
     * Get the full URL for the media asset.
     */
    public function getMediaUrlAttribute(): string
    {
        if (str_starts_with($this->media_path, 'http://') || str_starts_with($this->media_path, 'https://')) {
            return $this->media_path;
        }

        if (str_starts_with($this->media_path, 'videos/') || str_starts_with($this->media_path, 'images/')) {
            return asset($this->media_path);
        }

        return Storage::disk('public')->url($this->media_path);
    }
}
