<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\MonthlyProjectEvaluation;

class TestEvaluationCalculation extends Command
{
    protected $signature = 'evaluations:test {project-key} {month}';
    protected $description = 'Test evaluation calculation for a specific project and month';

    public function handle()
    {
        $projectKey = $this->argument('project-key');
        $month = $this->argument('month');

        $project = Project::where('key', $projectKey)->first();
        if (!$project) {
            $this->error("Project {$projectKey} not found");
            return 1;
        }

        // Get stored evaluation
        $stored = MonthlyProjectEvaluation::where('project_key', $projectKey)
            ->where('month_date', $month)
            ->first();

        if ($stored) {
            $this->info("Stored Evaluation for {$projectKey} - {$month}:");
            $this->line("  Asset Evaluation: " . $stored->asset_evaluation);
            $this->line("  Expense Asset: " . $stored->expense_asset);
            $this->line("  Revenue Asset: " . $stored->revenue_asset);
            $this->line("  Value Correction: " . $stored->value_correction);
            $this->line("  Previous Evaluation: " . $stored->previous_evaluation);
            $this->line("  Is After Exit: " . ($stored->is_after_exit ? 'Yes' : 'No'));
            
            // Verify calculation
            $calculated = $stored->previous_evaluation + $stored->expense_asset - $stored->revenue_asset + $stored->value_correction;
            if ($stored->is_after_exit) {
                $calculated = 0;
            }
            
            $this->line("  Calculated Check: " . $calculated);
            $this->line("  Match: " . ($calculated == $stored->asset_evaluation ? 'YES' : 'NO'));
        } else {
            $this->error("No stored evaluation found for {$projectKey} - {$month}");
        }

        return 0;
    }
}
