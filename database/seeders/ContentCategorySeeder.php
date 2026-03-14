<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContentCategory;

class ContentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'BECE',
                'slug' => 'bece',
            ],
            [
                'name' => 'WASSCE',
                'slug' => 'wassce',
            ],
            [
                'name' => 'Normal',
                'slug' => 'normal',
            ],
        ];

        foreach ($categories as $category) {
            ContentCategory::updateOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name']]
            );
        }
    }
}
