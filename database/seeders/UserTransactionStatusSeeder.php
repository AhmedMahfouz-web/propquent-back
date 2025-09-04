<?php

namespace Database\Seeders;

use App\Models\UserTransactionStatus;
use Illuminate\Database\Seeder;

class UserTransactionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'key' => 'pending',
                'name' => 'Pending',
                'description' => 'Transaction is pending approval or processing',
                'color' => 'warning',
                'sort_order' => 1,
            ],
            [
                'key' => 'processing',
                'name' => 'Processing',
                'description' => 'Transaction is being processed',
                'color' => 'info',
                'sort_order' => 2,
            ],
            [
                'key' => 'done',
                'name' => 'Completed',
                'description' => 'Transaction has been completed successfully',
                'color' => 'success',
                'sort_order' => 3,
            ],
            [
                'key' => 'cancelled',
                'name' => 'Cancelled',
                'description' => 'Transaction has been cancelled',
                'color' => 'danger',
                'sort_order' => 4,
            ],
            [
                'key' => 'failed',
                'name' => 'Failed',
                'description' => 'Transaction failed to process',
                'color' => 'danger',
                'sort_order' => 5,
            ],
            [
                'key' => 'on_hold',
                'name' => 'On Hold',
                'description' => 'Transaction is temporarily on hold',
                'color' => 'warning',
                'sort_order' => 6,
            ],
        ];

        foreach ($statuses as $status) {
            UserTransactionStatus::updateOrCreate(
                ['key' => $status['key']],
                $status
            );
        }
    }
}
