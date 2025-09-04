<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        Admin::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 'super_admin',
            'is_active' => true,
            'last_login_at' => now(),
        ]);


        $this->call([
            DeveloperSeeder::class,
            SystemConfigurationSeeder::class,
            UserTransactionTypeSeeder::class,
            UserTransactionStatusSeeder::class,
            UserSeeder::class,
            ProjectSeeder::class,
            ProjectTransactionSeeder::class,
            UserTransactionSeeder::class,
        ]);
    }
}
