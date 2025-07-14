<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'user:create-admin';
    protected $description = 'Creates the default admin user';

    public function handle()
    {
        if (Admin::where('email', 'admin@gmail.com')->exists()) {
            $this->info('Admin user already exists.');
            return;
        }

        Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'password_hash' => Hash::make('123456'),
        ]);

        $this->info('Admin user created successfully!');
    }
}
