<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 10; $i++) {
            User::create([
                'full_name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password_hash' => Hash::make('password'),
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
            ]);
        }
    }
}
