<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MonthlyProjectEvaluation;

class CompareAssetEvaluations extends Command
{
    protected $signature = 'evaluations:compare {month}';
    protected $description = 'Compare manual sum vs model method for asset evaluations';

    public function handle()
    {
        $month = $this->argument('month');

        $this->info("Comparing asset evaluations for {$month}:");
        $this->line("");

        // Method 1: Manual sum (what company report does now)
        $monthEvaluations = MonthlyProjectEvaluation::where('month_date', $month)
            ->get(['project_key', 'asset_evaluation']);

        $manualTotal = 0;
        $this->line("Individual Project Evaluations:");
        foreach ($monthEvaluations as $eval) {
            $this->line("  {$eval->project_key}: {$eval->asset_evaluation}");
            $manualTotal += $eval->asset_evaluation;
        }

        $this->line("");
        $this->line("Manual Sum Total: {$manualTotal}");

        // Method 2: Model method (original approach)
        $modelTotal = MonthlyProjectEvaluation::getCompanyAssetEvaluation($month);
        $this->line("Model Method Total: {$modelTotal}");

        // Method 3: Direct SQL
        $sqlTotal = MonthlyProjectEvaluation::where('month_date', $month)->sum('asset_evaluation');
        $this->line("Direct SQL SUM: {$sqlTotal}");

        $this->line("");
        $this->line("Record Count: " . $monthEvaluations->count());
        $this->line("Unique Projects: " . $monthEvaluations->pluck('project_key')->unique()->count());

        // Check for differences
        if ($manualTotal == $modelTotal && $modelTotal == $sqlTotal) {
            $this->info("✅ All methods match!");
        } else {
            $this->error("❌ Methods don't match!");
            $this->line("Difference between manual and model: " . ($manualTotal - $modelTotal));
            $this->line("Difference between manual and SQL: " . ($manualTotal - $sqlTotal));
        }

        return 0;
    }
}
