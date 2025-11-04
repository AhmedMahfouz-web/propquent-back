<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\MonthlyProjectEvaluation;
use Carbon\Carbon;

class DiagnoseEvaluationIssue extends Command
{
    protected $signature = 'evaluations:diagnose {project-key}';
    protected $description = 'Diagnose evaluation issues for a specific project';

    public function handle()
    {
        $projectKey = $this->argument('project-key');
        
        $project = Project::where('key', $projectKey)->first();
        
        if (!$project) {
            $this->error("Project not found: {$projectKey}");
            return 1;
        }

        $this->info("===========================================");
        $this->info("  PROJECT: {$project->title}");
        $this->info("===========================================");
        $this->newLine();

        // Project Status
        $this->info("PROJECT STATUS:");
        $this->line("  Status: {$project->status}");
        $this->line("  Exit Date: " . ($project->exit_date ?? 'N/A'));
        $this->line("  Stage: {$project->stage}");
        $this->newLine();

        // Latest Evaluation from Database
        $latestEvaluation = MonthlyProjectEvaluation::where('project_key', $projectKey)
            ->orderBy('month_date', 'desc')
            ->first();

        if ($latestEvaluation) {
            $this->info("LATEST EVALUATION IN DATABASE:");
            $this->line("  Month: {$latestEvaluation->month_date}");
            $this->line("  Asset Evaluation: $" . number_format($latestEvaluation->asset_evaluation, 2));
            $this->line("  Expense Asset: $" . number_format($latestEvaluation->expense_asset, 2));
            $this->line("  Revenue Asset: $" . number_format($latestEvaluation->revenue_asset, 2));
            $this->line("  Is After Exit: " . ($latestEvaluation->is_after_exit ? 'YES' : 'NO'));
            $this->newLine();
        } else {
            $this->error("NO EVALUATIONS FOUND IN DATABASE!");
            $this->newLine();
        }

        // Last 6 Months of Evaluations
        $recentEvaluations = MonthlyProjectEvaluation::where('project_key', $projectKey)
            ->orderBy('month_date', 'desc')
            ->limit(6)
            ->get();

        if ($recentEvaluations->count() > 0) {
            $this->info("LAST 6 MONTHS:");
            $this->table(
                ['Month', 'Asset Eval', 'Expense', 'Revenue', 'After Exit'],
                $recentEvaluations->map(function ($eval) {
                    return [
                        $eval->month_date,
                        '$' . number_format($eval->asset_evaluation, 2),
                        '$' . number_format($eval->expense_asset, 2),
                        '$' . number_format($eval->revenue_asset, 2),
                        $eval->is_after_exit ? 'YES' : 'NO',
                    ];
                })->toArray()
            );
            $this->newLine();
        }

        // Check for November 2025 specifically
        $nov2025 = MonthlyProjectEvaluation::where('project_key', $projectKey)
            ->where('month_date', '2025-11-01')
            ->first();

        if ($nov2025) {
            $this->info("NOVEMBER 2025 DATA:");
            $this->line("  Asset Evaluation: $" . number_format($nov2025->asset_evaluation, 2));
            $this->line("  Is After Exit: " . ($nov2025->is_after_exit ? 'YES' : 'NO'));
        } else {
            $this->warn("  November 2025 evaluation NOT FOUND in database");
        }
        $this->newLine();

        // Check project exit status vs evaluation
        if ($project->status === 'exited' && $latestEvaluation && $latestEvaluation->asset_evaluation > 0) {
            $this->error("⚠️  ISSUE DETECTED:");
            $this->error("    Project is marked as EXITED but evaluation is not $0");
            $this->error("    This suggests evaluation needs recalculation");
            $this->newLine();
        }

        if ($latestEvaluation && $latestEvaluation->asset_evaluation == 0 && $project->status !== 'exited') {
            $this->error("⚠️  ISSUE DETECTED:");
            $this->error("    Asset Evaluation is $0 but project is NOT marked as exited");
            $this->error("    Status: {$project->status}");
            
            if ($latestEvaluation->is_after_exit) {
                $this->error("    Database shows 'is_after_exit' = TRUE");
                $this->error("    But project exit_date is: " . ($project->exit_date ?? 'NULL'));
                $this->newLine();
                $this->info("POSSIBLE CAUSES:");
                $this->line("  1. Project exit_date was set but project status wasn't updated");
                $this->line("  2. Evaluation calculation used wrong exit logic");
                $this->line("  3. November hasn't been calculated yet (if today is early Nov)");
            }
            $this->newLine();
        }

        // Recommendation
        $this->info("RECOMMENDATION:");
        if ($latestEvaluation && $latestEvaluation->asset_evaluation == 0 && $project->status !== 'exited') {
            $this->line("  Run: php artisan evaluations:calculate --project-key={$projectKey} --force");
            $this->line("  This will recalculate using current project status");
        } else {
            $this->line("  Data looks consistent. Check report filtering or date range.");
        }
        $this->newLine();

        return 0;
    }
}
