<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectTransaction>
 */
class ProjectTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_key' => \App\Models\Project::inRandomOrder()->first()->key ?? \App\Models\Project::factory()->create()->key,
            'type' => $this->faker->randomElement(['Expense', 'Revenue']),
            'serving' => $this->faker->randomElement(['Asset', 'Operation']),
            'what_id' => \App\Models\TransactionWhat::inRandomOrder()->first()->id ?? \App\Models\TransactionWhat::factory(),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'due_date' => $this->faker->dateTimeBetween('-1 year', '+1 year'),
            'actual_date' => $this->faker->dateTimeBetween('-1 year', '+1 year'),
            'transaction_date' => $this->faker->dateTimeBetween('-1 year', '+1 year'),
            'method' => $this->faker->randomElement(['Cheque', 'Bank Transfer', 'Cash']),
            'reference_no' => $this->faker->uuid(),
            'status' => $this->faker->randomElement(['Done', 'Pending', 'Canceled']),
            'note' => $this->faker->sentence(),
        ];
    }
}
