<?php

namespace Database\Seeders;

use App\Models\HeroBanner;
use Illuminate\Database\Seeder;

class HeroBannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HeroBanner::firstOrCreate(
            ['title' => 'Explore & Learn'],
            [
                'subtitle' => 'at your own pace with interactive lessons.',
                'media_type' => 'video',
                'media_path' => 'videos/hero-video.mp4',
                'badge_text' => 'FEATURED',
                'cta_text' => 'Watch Now',
                'cta_url' => '/dashboard/digilearn',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        HeroBanner::firstOrCreate(
            ['title' => 'Afrimarker Showcase'],
            [
                'subtitle' => 'Enhancing learning through innovative digital tools.',
                'media_type' => 'image',
                'media_path' => 'images/hero/afrimarker_add.jpeg',
                'badge_text' => 'STAFF PICK',
                'cta_text' => 'Learn More',
                'cta_url' => '/dashboard/main',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );
    }
}
