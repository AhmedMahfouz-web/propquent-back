<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'developer_id' => \App\Models\Developer::inRandomOrder()->first()->id ?? \App\Models\Developer::factory(),
            'key' => $this->faker->uuid(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->word(),
            'unit_no' => $this->faker->word(),
            'project' => $this->faker->word(),
            'area' => $this->faker->numberBetween(50, 500),
            'garden_area' => $this->faker->numberBetween(10, 100),
            'bedrooms' => $this->faker->numberBetween(1, 5),
            'bathrooms' => $this->faker->numberBetween(1, 5),
            'floor' => $this->faker->word(),
            'location' => $this->faker->address(),
            'status' => $this->faker->randomElement(['On-Going', 'Exited']),
            'stage' => $this->faker->word(),
            'target_1' => $this->faker->word(),
            'target_2' => $this->faker->word(),
            'entry_date' => $this->faker->date(),
            'exit_date' => $this->faker->date(),
            'investment_type' => $this->faker->word(),
            'image_url' => $this->faker->imageUrl(),
            'document' => $this->faker->word(),
        ];
    }
}
