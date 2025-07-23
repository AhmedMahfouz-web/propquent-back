<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'image_path' => fake()->imageUrl(800, 600, 'architecture'),
            'image_name' => fake()->word() . '.jpg',
            'image_type' => 'image/jpeg',
            'image_size' => fake()->numberBetween(100000, 2000000),
            'alt_text' => fake()->sentence(3),
            'is_primary' => fake()->boolean(20), // 20% chance of being primary
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
