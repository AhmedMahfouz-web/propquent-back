<?php

namespace App\Observers;

use App\Models\ProjectTransaction;
use Illuminate\Support\Facades\Artisan;

class ProjectTransactionObserver
{
    /**
     * Handle the ProjectTransaction "created" event.
     */
    public function created(ProjectTransaction $projectTransaction): void
    {
        $this->updateEvaluations($projectTransaction);
    }

    /**
     * Handle the ProjectTransaction "updated" event.
     */
    public function updated(ProjectTransaction $projectTransaction): void
    {
        $this->updateEvaluations($projectTransaction);
    }

    /**
     * Handle the ProjectTransaction "deleted" event.
     */
    public function deleted(ProjectTransaction $projectTransaction): void
    {
        $this->updateEvaluations($projectTransaction);
    }

    /**
     * Update monthly evaluations for the affected project
     */
    private function updateEvaluations(ProjectTransaction $projectTransaction): void
    {
        // Update for both asset and operation transactions
        if (!in_array($projectTransaction->serving, ['asset', 'operation'])) {
            return;
        }

        // Prevent duplicate updates by using a simple lock mechanism
        $lockKey = "evaluation_update_{$projectTransaction->project_key}";
        
        if (cache()->has($lockKey)) {
            // Another update is already in progress for this project
            return;
        }

        // Set a 30-second lock
        cache()->put($lockKey, true, 30);

        try {
            // Get the month that needs updating
            $transactionDate = $projectTransaction->actual_date ?? $projectTransaction->transaction_date;
            $fromMonth = date('Y-m-01', strtotime($transactionDate));

            // Run the calculation command for this project from the affected month onwards
            Artisan::call('evaluations:calculate', [
                '--project-key' => $projectTransaction->project_key,
                '--from-month' => $fromMonth,
                '--force' => true,
            ]);
        } finally {
            // Release the lock
            cache()->forget($lockKey);
        }
    }
}
