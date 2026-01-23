<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PricingPlan;
use App\Models\PlanLevelGroup;

class PlanLevelGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define level group access for each plan
        $planAccessMappings = [
            'essential' => ['primary-lower', 'primary-upper', 'jhs'],
            'essential-plus' => ['primary-lower', 'primary-upper', 'jhs', 'shs'],
            'essential-pro' => ['primary-lower', 'primary-upper', 'jhs', 'shs', 'university'],
        ];

        // Get all pricing plans
        $plans = PricingPlan::all();

        foreach ($plans as $plan) {
            $slug = $plan->slug ?? strtolower(str_replace(' ', '-', $plan->name));

            if (isset($planAccessMappings[$slug])) {
                $levelGroups = $planAccessMappings[$slug];

                foreach ($levelGroups as $levelGroup) {
                    PlanLevelGroup::create([
                        'pricing_plan_id' => $plan->id,
                        'level_group' => $levelGroup,
                    ]);
                }
            }
        }
    }
}
