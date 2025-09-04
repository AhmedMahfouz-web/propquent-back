<?php

namespace Database\Seeders;

use App\Models\UserTransactionType;
use Illuminate\Database\Seeder;

class UserTransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'key' => 'deposit',
                'name' => 'Deposit',
                'description' => 'Money deposited into the system',
                'sort_order' => 1,
            ],
            [
                'key' => 'withdraw',
                'name' => 'Withdrawal',
                'description' => 'Money withdrawn from the system',
                'sort_order' => 2,
            ],
            [
                'key' => 'investment',
                'name' => 'Investment',
                'description' => 'Investment transaction',
                'sort_order' => 3,
            ],
            [
                'key' => 'dividend',
                'name' => 'Dividend',
                'description' => 'Dividend payment',
                'sort_order' => 4,
            ],
            [
                'key' => 'fee',
                'name' => 'Fee',
                'description' => 'Service or transaction fee',
                'sort_order' => 5,
            ],
        ];

        foreach ($types as $type) {
            UserTransactionType::updateOrCreate(
                ['key' => $type['key']],
                $type
            );
        }
    }
}
