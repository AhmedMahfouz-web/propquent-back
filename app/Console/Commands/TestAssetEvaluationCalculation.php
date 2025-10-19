<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\MonthlyProjectEvaluation;
use Carbon\Carbon;

class TestAssetEvaluationCalculation extends Command
{
    protected $signature = 'test:asset-evaluation {project-key?} {--months=6}';
    protected $description = 'Test asset evaluation calculation for debugging';

    public function handle()
    {
        $projectKey = $this->argument('project-key');
        $monthsCount = (int) $this->option('months');

        // Get a project to test with
        if ($projectKey) {
            $project = Project::where('key', $projectKey)->first();
            if (!$project) {
                $this->error("Project with key '{$projectKey}' not found.");
                return 1;
            }
        } else {
            $project = Project::has('transactions')->first();
            if (!$project) {
                $this->error("No projects with transactions found.");
                return 1;
            }
        }

        $this->info("Testing asset evaluation calculation for project: {$project->key} - {$project->title}");

        // Get recent months
        $months = collect();
        $current = Carbon::now()->startOfMonth();
        for ($i = 0; $i < $monthsCount; $i++) {
            $months->push($current->copy()->subMonths($i)->format('Y-m-01'));
        }
        $months = $months->reverse(); // Chronological order

        $this->info("Testing months: " . $months->implode(', '));

        // Get evaluations from database
        $evaluations = MonthlyProjectEvaluation::where('project_key', $project->key)
            ->whereIn('month_date', $months)
            ->orderBy('month_date')
            ->get();

        if ($evaluations->isEmpty()) {
            $this->warn("No evaluations found in database. Run 'php artisan evaluations:calculate' first.");
            return 1;
        }

        // Display the calculation chain
        $this->info("\nAsset Evaluation Calculation Chain:");
        $this->info("Formula: Asset Evaluation = Previous Month + Expense Asset - Revenue Asset + Value Correction");
        $this->info(str_repeat('-', 120));

        $previousEvaluation = 0;
        foreach ($evaluations as $evaluation) {
            $month = $evaluation->month_date->format('Y-m-01');
            
            // Verify the calculation
            $expectedEvaluation = $evaluation->previous_evaluation + $evaluation->expense_asset - $evaluation->revenue_asset + $evaluation->value_correction;
            $actualEvaluation = $evaluation->asset_evaluation;
            
            $status = abs($expectedEvaluation - $actualEvaluation) < 0.01 ? '✓' : '✗';
            
            $this->line(sprintf(
                "%s %s: %.2f + %.2f - %.2f + %.2f = %.2f (stored: %.2f) %s",
                $status,
                $month,
                $evaluation->previous_evaluation,
                $evaluation->expense_asset,
                $evaluation->revenue_asset,
                $evaluation->value_correction,
                $expectedEvaluation,
                $actualEvaluation,
                $status === '✗' ? '← MISMATCH!' : ''
            ));
            
            $previousEvaluation = $actualEvaluation;
        }

        // Test company total
        $this->info("\nCompany Total Asset Evaluation Test:");
        $this->info(str_repeat('-', 60));
        
        foreach ($months as $month) {
            $companyTotal = MonthlyProjectEvaluation::getCompanyAssetEvaluation($month);
            $manualTotal = MonthlyProjectEvaluation::where('month_date', $month)->sum('asset_evaluation');
            
            $status = abs($companyTotal - $manualTotal) < 0.01 ? '✓' : '✗';
            
            $this->line(sprintf(
                "%s %s: Company Method = %.2f, Manual Sum = %.2f %s",
                $status,
                $month,
                $companyTotal,
                $manualTotal,
                $status === '✗' ? '← MISMATCH!' : ''
            ));
        }

        return 0;
    }
}
