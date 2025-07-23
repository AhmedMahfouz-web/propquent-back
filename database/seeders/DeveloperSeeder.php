<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Developer;
use Faker\Factory as Faker;

class DeveloperSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 10; $i++) {
            Developer::create([
                'name' => $faker->company,
                'email' => $faker->unique()->companyEmail,
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'website' => 'https://' . $faker->domainName,
                'description' => $faker->paragraph,
            ]);
        }
    }
}
