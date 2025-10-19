<?php

namespace App\Observers;

use App\Models\Project;
use Illuminate\Support\Facades\Artisan;

class ProjectObserver
{
    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        // Check if status or exit_date changed
        if ($project->isDirty(['status', 'exit_date'])) {
            $this->updateEvaluations($project);
        }
    }

    /**
     * Update monthly evaluations for the affected project
     */
    private function updateEvaluations(Project $project): void
    {
        // Prevent duplicate updates by using a simple lock mechanism
        $lockKey = "evaluation_update_{$project->key}";
        
        if (cache()->has($lockKey)) {
            // Another update is already in progress for this project
            return;
        }

        // Set a 30-second lock
        cache()->put($lockKey, true, 30);

        try {
            // If project status changed to exited, we need to recalculate from exit date
            $fromMonth = null;
            if ($project->status === 'exited' && $project->exit_date) {
                $fromMonth = date('Y-m-01', strtotime($project->exit_date));
            }

            // Run the calculation command for this project
            $params = [
                '--project-key' => $project->key,
                '--force' => true,
            ];
            
            if ($fromMonth) {
                $params['--from-month'] = $fromMonth;
            }

            Artisan::call('evaluations:calculate', $params);
        } finally {
            // Release the lock
            cache()->forget($lockKey);
        }
    }
}
