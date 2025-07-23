<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserTransaction;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating user transactions...');

        $faker = Faker::create();
        $users = User::all();

        // Get dynamic configuration options
        $transactionTypes = $this->getConfigurationOptions('user_transaction_types');
        $transactionStatuses = $this->getConfigurationOptions('transaction_statuses');
        $transactionMethods = $this->getConfigurationOptions('transaction_methods');

        if (empty($transactionTypes) || empty($transactionStatuses) || empty($transactionMethods)) {
            $this->command->error('Please run SystemConfigurationSeeder first.');
            return;
        }

        foreach ($users as $user) {
            // Create between 1 and 10 transactions for each user
            $numberOfTransactions = $faker->numberBetween(1, 10);

            for ($i = 0; $i < $numberOfTransactions; $i++) {
                $transactionDate = $faker->dateTimeBetween($user->created_at, 'now');
                
                UserTransaction::create([
                    'user_id' => $user->id,
                    'transaction_type' => $faker->randomElement(['deposit', 'withdraw']),
                    'amount' => $faker->randomFloat(2, 10, 5000),
                    'transaction_date' => $transactionDate,
                    'actual_date' => $faker->optional(0.8)->dateTimeBetween($transactionDate, 'now'),
                    'method' => $faker->randomElement($transactionMethods),
                    'reference_no' => 'UTX-' . $faker->unique()->numerify('##########'),
                    'note' => $faker->optional(0.5)->sentence,
                    'status' => $faker->randomElement($transactionStatuses),
                    'created_at' => $transactionDate,
                    'updated_at' => $transactionDate,
                ]);
            }
        }

        $this->command->info('User transactions created successfully.');
    }

    /**
     * Get configuration options with fallbacks
     */
    private function getConfigurationOptions(string $category): array
    {
        try {
            $options = array_keys(\App\Models\SystemConfiguration::getOptions($category));

            if (empty($options)) {
                $this->command->warn("Could not load {$category} from configuration. Using defaults.");
                return $this->getDefaultOptions($category);
            }

            return $options;
        } catch (\Exception) {
            $this->command->warn("Could not load {$category} from configuration. Using defaults.");
            return $this->getDefaultOptions($category);
        }
    }

    private function getDefaultOptions(string $category): array
    {
        return match ($category) {
            'user_transaction_types' => ['deposit', 'withdrawal', 'fee', 'refund', 'commission'],
            'transaction_statuses' => ['done', 'pending', 'cancelled'],
            'transaction_methods' => ['bank_transfer', 'credit_card', 'paypal', 'stripe'],
            default => [],
        };
    }
}
