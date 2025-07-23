<?php

namespace Database\Factories;

use App\Models\Developer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'title' => fake()->sentence(3),
            'developer_id' => Developer::factory(),
            'location' => fake()->city(),
            'type' => fake()->randomElement(['apartment', 'villa', 'townhouse', 'penthouse']),
            'unit_no' => fake()->buildingNumber(),
            'project' => fake()->company() . ' Project',
            'area' => fake()->randomFloat(2, 50, 500),
            'garden_area' => fake()->randomFloat(2, 0, 100) . ' sqm',
            'bedrooms' => fake()->numberBetween(1, 5),
            'bathrooms' => fake()->numberBetween(1, 4),
            'floor' => fake()->numberBetween(1, 20),
            'status' => fake()->randomElement(['on-going', 'exited']),
            'stage' => fake()->randomElement(['planning', 'construction', 'completed', 'delivered']),
            'target_1' => fake()->date(),
            'target_2' => fake()->date(),
            'entry_date' => fake()->date(),
            'exit_date' => fake()->date(),
            'investment_type' => fake()->randomElement(['buy_to_let', 'capital_growth', 'mixed']),
            'document' => fake()->word() . '.pdf',
        ];
    }
}
