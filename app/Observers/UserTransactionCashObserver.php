<?php

namespace App\Observers;

use App\Models\UserTransaction;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UserTransactionCashObserver
{
    /**
     * Handle the UserTransaction "saved" event (covers created and updated).
     */
    public function saved(UserTransaction $userTransaction): void
    {
        $this->updateCashBalance($userTransaction);
    }

    /**
     * Handle the UserTransaction "deleted" event.
     */
    public function deleted(UserTransaction $userTransaction): void
    {
        $this->updateCashBalance($userTransaction);
    }

    /**
     * Update cash balance cache for affected months
     */
    private function updateCashBalance(UserTransaction $transaction): void
    {
        try {
            // Get the month of this transaction
            $month = $transaction->transaction_date->format('Y-m-01');
            
            // Use cache lock to prevent duplicate updates
            $lockKey = "cash_update_{$month}";
            
            if (Cache::lock($lockKey, 10)->get()) {
                // Calculate from this month onwards to ensure cumulative accuracy
                Artisan::call('cash:calculate', [
                    '--from' => $month,
                    '--to' => now()->format('Y-m-01'),
                    '--force' => true
                ]);
                
                Cache::lock($lockKey)->release();
                
                Log::info("Cash balance updated for user transaction", [
                    'transaction_id' => $transaction->id,
                    'month' => $month,
                    'amount' => $transaction->amount,
                    'type' => $transaction->transaction_type
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to update cash balance for user transaction", [
                'transaction_id' => $transaction->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }
}
