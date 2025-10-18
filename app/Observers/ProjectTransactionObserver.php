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
        // Only update for asset transactions
        if ($projectTransaction->serving !== 'asset') {
            return;
        }

        // Get the month that needs updating
        $transactionDate = $projectTransaction->actual_date ?? $projectTransaction->transaction_date;
        $fromMonth = date('Y-m-01', strtotime($transactionDate));

        // Run the calculation command for this project from the affected month onwards
        Artisan::call('evaluations:calculate', [
            '--project-key' => $projectTransaction->project_key,
            '--from-month' => $fromMonth,
            '--force' => true,
        ]);
    }
}
