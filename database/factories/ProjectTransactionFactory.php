<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\TransactionWhat;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_key' => Project::factory(),
            'type' => fake()->randomElement(['payment', 'refund', 'fee', 'deposit']),
            'serving' => fake()->randomElement(['buyer', 'seller', 'agent', 'developer']),
            'what_id' => TransactionWhat::factory(),
            'amount' => fake()->randomFloat(2, 100, 50000),
            'due_date' => fake()->date(),
            'actual_date' => fake()->optional()->date(),
            'transaction_date' => fake()->date(),
            'method' => fake()->randomElement(['cash', 'bank_transfer', 'cheque', 'card']),
            'reference_no' => fake()->unique()->numerify('TXN-#######'),
            'status' => fake()->randomElement(['pending', 'completed', 'cancelled', 'failed']),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
