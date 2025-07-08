<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StatusChange>
 */
class StatusChangeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => \App\Models\Project::factory(),
            'from_status' => $this->faker->word(),
            'to_status' => $this->faker->word(),
            'change_date' => $this->faker->dateTimeThisYear(),
        ];
    }
}
