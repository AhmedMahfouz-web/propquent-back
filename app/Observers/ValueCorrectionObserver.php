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
        // Recalculate the entire project to ensure proper cumulative calculation
        // This is necessary because value corrections can affect the cumulative chain
        Artisan::call('evaluations:calculate', [
            '--project-key' => $valueCorrection->project_key,
            '--force' => true,
        ]);
    }
}
