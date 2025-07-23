<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AdminFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('123456'),
            'role' => fake()->randomElement(['admin', 'super_admin', 'manager']),
            'is_active' => true,
            'last_login_at' => fake()->optional()->dateTime(),
        ];
    }
}
