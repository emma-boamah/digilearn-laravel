<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LevelArchitectureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'title' => 'Grade/Primary 1-3',
                'slug' => 'primary-lower',
                'description' => 'Lower primary or Elementary school',
                'has_illustration' => false,
                'display_order' => 1,
                'levels' => [
                    ['title' => 'Primary 1', 'slug' => 'primary-1', 'description' => 'Foundation learning for young minds', 'rank' => 1],
                    ['title' => 'Primary 2', 'slug' => 'primary-2', 'description' => 'Building on fundamentals', 'rank' => 2],
                    ['title' => 'Primary 3', 'slug' => 'primary-3', 'description' => 'Developing critical thinking skills', 'rank' => 3],
                ]
            ],
            [
                'title' => 'Grade/Primary 4-6',
                'slug' => 'primary-upper',
                'description' => 'Upper primary or elementary school',
                'has_illustration' => false,
                'display_order' => 2,
                'levels' => [
                    ['title' => 'Primary 4', 'slug' => 'primary-4', 'description' => 'Advanced primary education', 'rank' => 4],
                    ['title' => 'Primary 5', 'slug' => 'primary-5', 'description' => 'Preparing for junior high transition', 'rank' => 5],
                    ['title' => 'Primary 6', 'slug' => 'primary-6', 'description' => 'BECE preparation focus', 'rank' => 6],
                ]
            ],
            [
                'title' => 'Grade 7-9/JHS 1-3',
                'slug' => 'jhs',
                'description' => 'Junior High School or Middle school',
                'has_illustration' => true,
                'display_order' => 3,
                'levels' => [
                    ['title' => 'JHS 1', 'slug' => 'jhs-1', 'description' => 'Introduction to junior high curriculum', 'rank' => 7],
                    ['title' => 'JHS 2', 'slug' => 'jhs-2', 'description' => 'Intermediate junior high studies', 'rank' => 8],
                    ['title' => 'JHS 3', 'slug' => 'jhs-3', 'description' => 'Final JHS year with BECE preparation', 'rank' => 9],
                ]
            ],
            [
                'title' => 'Grade 10-12/SHS 1-3',
                'slug' => 'shs',
                'description' => 'High school or Senior High School',
                'has_illustration' => false,
                'display_order' => 4,
                'levels' => [
                    ['title' => 'SHS 1', 'slug' => 'shs-1', 'description' => 'Senior high foundation with specialized tracks', 'rank' => 10],
                    ['title' => 'SHS 2', 'slug' => 'shs-2', 'description' => 'Advanced senior high studies', 'rank' => 11],
                    ['title' => 'SHS 3', 'slug' => 'shs-3', 'description' => 'Final SHS year with WASSCE preparation', 'rank' => 12],
                ]
            ],
            [
                'title' => 'University',
                'slug' => 'university',
                'description' => 'Higher education with specialized programs and courses',
                'has_illustration' => true,
                'display_order' => 5,
                'levels' => [
                    ['title' => 'University Year 1', 'slug' => 'uni-1', 'description' => 'First year undergraduate programs and foundation courses', 'rank' => 13],
                    ['title' => 'University Year 2', 'slug' => 'uni-2', 'description' => 'Second year undergraduate programs with specialized tracks', 'rank' => 14],
                    ['title' => 'University Year 3', 'slug' => 'uni-3', 'description' => 'Third year undergraduate programs with advanced coursework', 'rank' => 15],
                    ['title' => 'University Year 4', 'slug' => 'uni-4', 'description' => 'Final year undergraduate programs and capstone projects', 'rank' => 16],
                ]
            ],
        ];

        foreach ($groups as $groupData) {
            $levels = $groupData['levels'];
            unset($groupData['levels']);

            $group = \App\Models\LevelGroup::create($groupData);

            foreach ($levels as $levelData) {
                $group->levels()->create($levelData);
            }
        }

        // Associate Pricing Plans with Level Groups
        $essential = \App\Models\PricingPlan::where('name', 'Essential')->first();
        $plus = \App\Models\PricingPlan::where('name', 'Essential Plus')->first();
        $pro = \App\Models\PricingPlan::where('name', 'Essential Pro')->first();

        if ($essential) {
            $essential->belongsToMany(\App\Models\LevelGroup::class, 'plan_level_group_new')->attach(
                \App\Models\LevelGroup::whereIn('slug', ['primary-lower'])->pluck('id')
            );
        }

        if ($plus) {
            $plus->belongsToMany(\App\Models\LevelGroup::class, 'plan_level_group_new')->attach(
                \App\Models\LevelGroup::whereIn('slug', ['primary-lower', 'primary-upper', 'jhs'])->pluck('id')
            );
        }

        if ($pro) {
            $pro->belongsToMany(\App\Models\LevelGroup::class, 'plan_level_group_new')->attach(
                \App\Models\LevelGroup::pluck('id')
            );
        }
    }
}
