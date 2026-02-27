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
        'display_order'
    ];

    /** @var array */
    protected $casts = [
        'has_illustration' => 'boolean',
    ];

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
