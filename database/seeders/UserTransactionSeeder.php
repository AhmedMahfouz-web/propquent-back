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

        foreach ($users as $user) {
            // Create between 1 and 10 transactions for each user
            $numberOfTransactions = $faker->numberBetween(1, 10);

            for ($i = 0; $i < $numberOfTransactions; $i++) {
                $transactionDate = $faker->dateTimeBetween($user->created_at, 'now');
                
                UserTransaction::create([
                    'user_id' => $user->id,
                    'transaction_type' => $faker->randomElement([UserTransaction::TYPE_DEPOSIT, UserTransaction::TYPE_WITHDRAWAL]),
                    'amount' => $faker->randomFloat(2, 10, 5000),
                    'transaction_date' => $transactionDate,
                    'actual_date' => $faker->optional(0.8)->dateTimeBetween($transactionDate, 'now'),
                    'method' => $faker->randomElement(array_keys(UserTransaction::getAvailableMethods())),
                    'reference_no' => 'UTX-' . $faker->unique()->numerify('##########'),
                    'note' => $faker->optional(0.5)->sentence,
                    'status' => $faker->randomElement([UserTransaction::STATUS_PENDING, UserTransaction::STATUS_DONE, UserTransaction::STATUS_CANCELLED]),
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
