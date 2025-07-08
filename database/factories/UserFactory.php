<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password_hash' => static::$password ??= Hash::make('password'),
            'auth_provider' => $this->faker->randomElement(['email', 'google', 'apple']),
            'provider_user_id' => $this->faker->uuid(),
            'email_verified' => $this->faker->boolean(),
            'phone_number' => $this->faker->phoneNumber(),
            'country' => $this->faker->country(),
            'profile_picture_url' => $this->faker->imageUrl(),
            'is_active' => $this->faker->boolean(),
            'last_login_at' => $this->faker->dateTimeThisYear(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
