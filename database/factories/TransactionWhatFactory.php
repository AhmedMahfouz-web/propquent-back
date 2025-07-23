<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionWhatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Down Payment', 'Monthly Payment', 'Maintenance Fee', 'Service Charge', 'Registration Fee']),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement(['payment', 'fee', 'charge', 'deposit']),
            'is_active' => true,
        ];
    }
}
