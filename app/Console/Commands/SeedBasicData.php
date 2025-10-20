<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemConfiguration;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SeedBasicData extends Command
{
    protected $signature = 'seed:basic-data';
    protected $description = 'Seed basic data required for user transactions';

    public function handle()
    {
        $this->info('Seeding basic data for user transactions...');
        
        // Seed System Configurations
        $this->info('Seeding system configurations...');
        $this->seedSystemConfigurations();
        
        // Check if users exist, if not create a test user
        $userCount = User::count();
        if ($userCount === 0) {
            $this->info('No users found. Creating a test user...');
            $this->createTestUser();
        } else {
            $this->info("Found {$userCount} users in the system.");
        }
        
        $this->info('Basic data seeding completed!');
        $this->info('You can now create user transactions.');
        
        return 0;
    }
    
    private function seedSystemConfigurations()
    {
        $configurations = [
            // General Transaction Statuses
            ['category' => 'transaction_statuses', 'key' => 'done', 'value' => 'Done', 'label' => 'Done'],
            ['category' => 'transaction_statuses', 'key' => 'pending', 'value' => 'Pending', 'label' => 'Pending'],
            ['category' => 'transaction_statuses', 'key' => 'cancelled', 'value' => 'Cancelled', 'label' => 'Cancelled'],

            // General Transaction Methods
            ['category' => 'transaction_methods', 'key' => 'bank_transfer', 'value' => 'Bank Transfer', 'label' => 'Bank Transfer'],
            ['category' => 'transaction_methods', 'key' => 'check', 'value' => 'Check', 'label' => 'Check'],
            ['category' => 'transaction_methods', 'key' => 'cash', 'value' => 'Cash', 'label' => 'Cash'],
            ['category' => 'transaction_methods', 'key' => 'credit_card', 'value' => 'Credit Card', 'label' => 'Credit Card'],
            ['category' => 'transaction_methods', 'key' => 'instapay', 'value' => 'Instapay', 'label' => 'Instapay'],
        ];

        foreach ($configurations as $config) {
            SystemConfiguration::updateOrCreate(
                [
                    'category' => $config['category'],
                    'key' => $config['key'],
                ],
                array_merge($config, [
                    'is_active' => true,
                ])
            );
        }
        
        $this->info('System configurations seeded successfully.');
    }
    
    private function createTestUser()
    {
        User::create([
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '123456789',
            'password' => Hash::make('password'),
            'custom_id' => 'inv-1',
        ]);
        
        $this->info('Test user created: test@example.com (password: password)');
    }
}
