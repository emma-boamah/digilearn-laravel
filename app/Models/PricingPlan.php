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
        'features',
        'discount_tiers',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'discount_tiers' => 'array',
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

    /**
     * Get the price for a specific duration, applying discounts if available
     */
    public function getPriceForDuration(string $duration): float
    {
        $months = match ($duration) {
            'trial' => 0,
            'month' => 1,
            '3month' => 3,
            '6month' => 6,
            '12month' => 12,
            default => 1,
        };

        if ($months === 0) {
            return 0;
        }

        $basePrice = $this->price * $months;

        if (!$this->discount_tiers) {
            return $basePrice;
        }

        foreach ($this->discount_tiers as $tier) {
            if ((int)($tier['duration_months'] ?? 0) === $months) {
                if (isset($tier['discount_percentage'])) {
                    $discountPercentage = $tier['discount_percentage'] ?? 0;
                    return $basePrice * (1 - $discountPercentage / 100);
                } elseif (isset($tier['price_per_month'])) {
                    return ($tier['price_per_month'] ?? $this->price) * $months;
                }
            }
        }

        return $basePrice;
    }

    /**
     * Get formatted price for a specific duration
     */
    public function getFormattedPriceForDuration(string $duration): string
    {
        $price = $this->getPriceForDuration($duration);
        return $this->currency . ' ' . number_format($price, 2);
    }

    /**
     * Get level groups accessible by this plan (Old version)
     */
    public function accessibleLevelGroups()
    {
        return $this->hasMany(PlanLevelGroup::class, 'pricing_plan_id');
    }

    /**
     * Get level groups accessible by this plan (New database-driven version)
     */
    public function levelGroups()
    {
        return $this->belongsToMany(LevelGroup::class, 'plan_level_group_new');
    }

    /**
     * Check if plan can access a specific level group
     */
    public function canAccessLevelGroup($groupId)
    {
        // Try new database-driven approach first
        if ($this->levelGroups()->where('slug', $groupId)->exists()) {
            return true;
        }

        // Fallback to old string-based approach
        return $this->accessibleLevelGroups()
            ->where('level_group', $groupId)
            ->exists();
    }
}
