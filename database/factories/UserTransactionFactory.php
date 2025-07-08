<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserTransaction>
 */
class UserTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'type' => $this->faker->randomElement(['Deposit', 'Withdraw']),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'transaction_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'actual_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'method' => $this->faker->randomElement(['Cheque', 'Bank Transfer', 'Cash']),
            'reference_no' => $this->faker->uuid(),
            'note' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['Done', 'Pending', 'Canceled']),
        ];
    }
}
