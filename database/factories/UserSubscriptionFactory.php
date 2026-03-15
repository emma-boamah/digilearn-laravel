<?php

namespace Database\Factories;

use App\Models\UserSubscription;
use App\Models\User;
use App\Models\PricingPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserSubscriptionFactory extends Factory
{
    protected $model = UserSubscription::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'pricing_plan_id' => PricingPlan::factory(),
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => now()->addMonth(),
        ];
    }
}
