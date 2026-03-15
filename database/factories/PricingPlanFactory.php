<?php

namespace Database\Factories;

use App\Models\PricingPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PricingPlanFactory extends Factory
{
    protected $model = PricingPlan::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'slug' => $this->faker->unique()->slug,
            'price' => $this->faker->randomFloat(2, 10, 100),
            'currency' => 'GHS',
            'period' => 'month',
            'is_active' => true,
            'features' => ['feature1', 'feature2'],
        ];
    }
}
