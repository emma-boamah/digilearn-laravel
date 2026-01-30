<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoFactory extends Factory
{
    protected $model = Video::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'video_path' => 'videos/sample.mp4',
            'grade_level' => 'Primary 3',
            'subject_id' => \App\Models\Subject::factory(),
            'duration_seconds' => $this->faker->numberBetween(60, 600),
            'description' => $this->faker->paragraph,
            'is_featured' => $this->faker->boolean(10),
            'status' => 'approved',
            'uploaded_by' => User::factory(),
            'views' => $this->faker->numberBetween(0, 5000),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function university(): static
    {
        return $this->state(fn (array $attributes) => [
            'grade_level' => 'University',
        ]);
    }
}
