<?php

namespace App\Observers;

use App\Models\ValueCorrection;
use Illuminate\Support\Facades\Artisan;

class ValueCorrectionObserver
{
    /**
     * Handle the ValueCorrection "created" event.
     */
    public function created(ValueCorrection $valueCorrection): void
    {
        $this->updateEvaluations($valueCorrection);
    }

    /**
     * Handle the ValueCorrection "updated" event.
     */
    public function updated(ValueCorrection $valueCorrection): void
    {
        $this->updateEvaluations($valueCorrection);
    }

    /**
     * Handle the ValueCorrection "deleted" event.
     */
    public function deleted(ValueCorrection $valueCorrection): void
    {
        $this->updateEvaluations($valueCorrection);
    }

    /**
     * Update monthly evaluations for the affected project
     */
    private function updateEvaluations(ValueCorrection $valueCorrection): void
    {
        // Get the month that needs updating
        $correctionDate = $valueCorrection->correction_date;
        $fromMonth = date('Y-m-01', strtotime($correctionDate));

        // Run the calculation command for this project from the affected month onwards
        Artisan::call('evaluations:calculate', [
            '--project-key' => $valueCorrection->project_key,
            '--from-month' => $fromMonth,
            '--force' => true,
        ]);
    }
}
