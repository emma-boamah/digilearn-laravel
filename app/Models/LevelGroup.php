<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LevelGroup extends Model
{
    /** @var array */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'has_illustration',
        'display_order',
        'is_active'
    ];

    /** @var array */
    protected $casts = [
        'has_illustration' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Scope to only include active level groups.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the levels for this group.
     */
    public function levels()
    {
        return $this->hasMany(Level::class)->orderBy('rank');
    }

    /**
     * The pricing plans that belong to the level group.
     */
    public function pricingPlans()
    {
        return $this->belongsToMany(\App\Models\PricingPlan::class, 'plan_level_group_new');
    }
}
