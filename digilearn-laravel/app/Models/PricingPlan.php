<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'currency',
        'period',
        'description',
        'duration_days',
        'features',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Get users subscribed to this plan
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get active users subscribed to this plan
     */
    public function activeSubscriptions()
    {
        return $this->hasMany(UserSubscription::class)->where('status', 'active');
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return $this->currency . ' ' . number_format($this->price, 2);
    }

    /**
     * Scope for active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered plans
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }
}
