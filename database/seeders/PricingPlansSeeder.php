<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PricingPlan;

class PricingPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Essential',
                'slug' => 'essential',
                'price' => 50.00,
                'currency' => 'GHS',
                'period' => 'monthly',
                'description' => 'Perfect for students who want access to basic learning resources and DigiLearn platform.',
                'features' => [
                    'Access to DigiLearn platform',
                    'Basic learning resources',
                    'Community support'
                ],
                'duration_days' => 30,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Extra Tuition',
                'slug' => 'extra-tuition',
                'price' => 200.00,
                'currency' => 'GHS',
                'period' => 'monthly',
                'description' => 'Comprehensive learning package with personalized tuition and advanced resources.',
                'features' => [
                    'Access to DigiLearn platform',
                    'Join live classes',
                    'Learning Resources',
                    'Personalized class sessions',
                    '24/7 service support'
                ],
                'duration_days' => 30,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Home School',
                'slug' => 'home-school',
                'price' => 200.00,
                'currency' => 'GHS',
                'period' => 'monthly',
                'description' => 'Complete home schooling solution with personalized curriculum and dedicated support.',
                'features' => [
                    'Access to DigiLearn platform',
                    'Personalized tuition sessions',
                    'Learning Resources',
                    'Sample Questions & Assessments',
                    '24/7 service support'
                ],
                'duration_days' => 30,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            PricingPlan::create($plan);
        }
    }
}