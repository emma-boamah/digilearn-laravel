<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanLevelGroup extends Model
{
    protected $table = 'plan_level_group';

    protected $fillable = [
        'pricing_plan_id',
        'level_group',
    ];

    /**
     * Get the pricing plan that owns this level group access
     */
    public function pricingPlan()
    {
        return $this->belongsTo(PricingPlan::class);
    }
}
