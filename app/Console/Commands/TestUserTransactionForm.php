<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemConfiguration;
use App\Models\UserTransaction;
use App\Models\User;

class TestUserTransactionForm extends Command
{
    protected $signature = 'test:user-transaction-form';
    protected $description = 'Test UserTransaction form dependencies';

    public function handle()
    {
        $this->info('Testing UserTransaction form dependencies...');
        
        // Test SystemConfiguration data
        $this->info("\n=== Transaction Statuses ===");
        $statuses = SystemConfiguration::getOptions('transaction_statuses');
        if (empty($statuses)) {
            $this->error('No transaction statuses found in system_configurations table!');
        } else {
            foreach ($statuses as $key => $label) {
                $this->line("$key => $label");
            }
        }
        
        $this->info("\n=== Transaction Methods ===");
        $methods = SystemConfiguration::getOptions('transaction_methods');
        if (empty($methods)) {
            $this->error('No transaction methods found in system_configurations table!');
        } else {
            foreach ($methods as $key => $label) {
                $this->line("$key => $label");
            }
        }
        
        // Test UserTransaction methods
        $this->info("\n=== UserTransaction Methods ===");
        try {
            $transactionTypes = UserTransaction::getAvailableTransactionTypes();
            $this->info('Transaction Types: ' . json_encode($transactionTypes));
            
            $availableStatuses = UserTransaction::getAvailableStatuses();
            $this->info('Available Statuses: ' . json_encode($availableStatuses));
            
            $availableMethods = UserTransaction::getAvailableMethods();
            $this->info('Available Methods: ' . json_encode($availableMethods));
        } catch (\Exception $e) {
            $this->error('Error calling UserTransaction methods: ' . $e->getMessage());
        }
        
        // Test Users exist
        $this->info("\n=== Users ===");
        $userCount = User::count();
        $this->info("Total users: $userCount");
        
        if ($userCount === 0) {
            $this->error('No users found! You need users to create transactions.');
        } else {
            $users = User::take(3)->get(['id', 'full_name', 'email']);
            foreach ($users as $user) {
                $this->line("ID: {$user->id}, Name: {$user->full_name}, Email: {$user->email}");
            }
        }
        
        $this->info("\n=== Summary ===");
        if (empty($statuses) || empty($methods) || $userCount === 0) {
            $this->error('Issues found! Run the following commands to fix:');
            if (empty($statuses) || empty($methods)) {
                $this->line('php artisan db:seed --class=SystemConfigurationSeeder');
            }
            if ($userCount === 0) {
                $this->line('php artisan db:seed --class=UserSeeder');
            }
        } else {
            $this->info('All dependencies look good!');
        }
        
        return 0;
    }
}
