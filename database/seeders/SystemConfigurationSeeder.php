<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemConfiguration;

class SystemConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configurations = [
            // Project Statuses
            ['category' => 'project_statuses', 'key' => 'on-going', 'value' => 'On-going', 'label' => 'On-going'],
            ['category' => 'project_statuses', 'key' => 'exited', 'value' => 'Exited', 'label' => 'Exited'],

            // Project Stages
            ['category' => 'project_stages', 'key' => 'planning', 'value' => 'Planning', 'label' => 'Planning'],
            ['category' => 'project_stages', 'key' => 'construction', 'value' => 'Construction', 'label' => 'Construction'],
            ['category' => 'project_stages', 'key' => 'completed', 'value' => 'Completed', 'label' => 'Completed'],
            ['category' => 'project_stages', 'key' => 'delivered', 'value' => 'Delivered', 'label' => 'Delivered'],

            // Project Transaction Types
            ['category' => 'project_transaction_types', 'key' => 'expense', 'value' => 'Expense', 'label' => 'Expense'],
            ['category' => 'project_transaction_types', 'key' => 'revenue', 'value' => 'Revenue', 'label' => 'Revenue'],

            // User Transaction Types
            ['category' => 'user_transaction_types', 'key' => 'deposit', 'value' => 'Deposit', 'label' => 'Deposit'],
            ['category' => 'user_transaction_types', 'key' => 'withdrawal', 'value' => 'Withdrawal', 'label' => 'Withdrawal'],

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

            // Project Transaction Serving
            ['category' => 'transaction_serving', 'key' => 'asset', 'value' => 'Asset', 'label' => 'Asset'],
            ['category' => 'transaction_serving', 'key' => 'operation', 'value' => 'Operation', 'label' => 'Operation'],
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
    }
}
