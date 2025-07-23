<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password_hash' => static::$password ??= Hash::make('password'),
            'auth_provider' => null,
            'provider_user_id' => null,
            'email_verified' => fake()->boolean(),
            'phone_number' => fake()->phoneNumber(),
            'country' => fake()->country(),
            'profile_picture_url' => fake()->imageUrl(),
            'is_active' => true,
            'last_login_at' => fake()->dateTime(),
            'theme_color' => fake()->hexColor(),
            'custom_theme_color' => fake()->hexColor(),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified' => false,
        ]);
    }
}
