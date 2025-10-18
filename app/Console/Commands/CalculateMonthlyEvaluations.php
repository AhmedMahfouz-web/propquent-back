<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\ProjectTransaction;
use App\Models\ValueCorrection;
use App\Models\MonthlyProjectEvaluation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CalculateMonthlyEvaluations extends Command
{
    protected $signature = 'evaluations:calculate 
                          {--project-key= : Calculate for specific project only}
                          {--from-month= : Start from specific month (Y-m-01 format)}
                          {--to-month= : End at specific month (Y-m-01 format)}
                          {--force : Recalculate existing evaluations}';

    protected $description = 'Calculate and store monthly project asset evaluations';

    public function handle()
    {
        $this->info('Starting monthly evaluations calculation...');

        $projectKey = $this->option('project-key');
        $fromMonth = $this->option('from-month');
        $toMonth = $this->option('to-month');
        $force = $this->option('force');

        // Get projects to process
        $projectsQuery = Project::with(['transactions', 'valueCorrections']);
        if ($projectKey) {
            $projectsQuery->where('key', $projectKey);
        }
        $projects = $projectsQuery->get();

        if ($projects->isEmpty()) {
            $this->error('No projects found to process.');
            return 1;
        }

        $this->info("Processing {$projects->count()} projects...");

        foreach ($projects as $project) {
            $this->processProject($project, $fromMonth, $toMonth, $force);
        }

        $this->info('Monthly evaluations calculation completed!');
        return 0;
    }

    private function processProject(Project $project, ?string $fromMonth, ?string $toMonth, bool $force): void
    {
        $this->line("Processing project: {$project->key} - {$project->title}");

        // Get date range for this project
        $dateRange = $this->getProjectDateRange($project, $fromMonth, $toMonth);
        
        if (empty($dateRange)) {
            $this->line("  No transactions found for project {$project->key}");
            return;
        }

        // Delete existing evaluations if force is enabled
        if ($force) {
            MonthlyProjectEvaluation::where('project_key', $project->key)
                ->whereBetween('month_date', [$dateRange['start'], $dateRange['end']])
                ->delete();
        }

        // Generate all months in range
        $months = $this->generateMonthRange($dateRange['start'], $dateRange['end']);
        
        // Calculate evaluations month by month
        $previousEvaluation = 0;
        
        foreach ($months as $month) {
            $evaluation = $this->calculateMonthEvaluation($project, $month, $previousEvaluation, $force);
            if ($evaluation !== null) {
                $previousEvaluation = $evaluation['asset_evaluation'];
            }
        }

        $this->line("  Processed {$project->key}: " . count($months) . " months");
    }

    private function getProjectDateRange(Project $project, ?string $fromMonth, ?string $toMonth): array
    {
        // Get earliest and latest transaction dates
        $earliestTransaction = $project->transactions()
            ->where('status', 'done')
            ->where('serving', 'asset')
            ->orderBy('transaction_date', 'asc')
            ->first();

        $latestTransaction = $project->transactions()
            ->where('status', 'done')
            ->where('serving', 'asset')
            ->orderBy('transaction_date', 'desc')
            ->first();

        if (!$earliestTransaction) {
            return [];
        }

        $startDate = $fromMonth ? Carbon::parse($fromMonth) : Carbon::parse($earliestTransaction->transaction_date)->startOfMonth();
        $endDate = $toMonth ? Carbon::parse($toMonth) : Carbon::now()->startOfMonth();

        return [
            'start' => $startDate->format('Y-m-01'),
            'end' => $endDate->format('Y-m-01'),
        ];
    }

    private function generateMonthRange(string $start, string $end): array
    {
        $months = [];
        $current = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        while ($current <= $endDate) {
            $months[] = $current->format('Y-m-01');
            $current->addMonth();
        }

        return $months;
    }

    private function calculateMonthEvaluation(Project $project, string $month, float $previousEvaluation, bool $force): ?array
    {
        // Check if already exists and not forcing
        if (!$force) {
            $existing = MonthlyProjectEvaluation::where('project_key', $project->key)
                ->where('month_date', $month)
                ->first();
            
            if ($existing) {
                return [
                    'asset_evaluation' => (float) $existing->asset_evaluation,
                ];
            }
        }

        // Get transactions for this month
        $monthStart = Carbon::parse($month)->startOfMonth();
        $monthEnd = Carbon::parse($month)->endOfMonth();
        $today = now()->startOfDay();

        $expenseAsset = $project->transactions()
            ->where('status', 'done')
            ->where('financial_type', 'expense')
            ->where('serving', 'asset')
            ->where(function($query) use ($monthStart, $monthEnd, $today) {
                $query->where(function($q) use ($monthStart, $monthEnd, $today) {
                    // Done transactions with actual_date in this month and <= today
                    $q->whereNotNull('actual_date')
                      ->whereBetween('actual_date', [$monthStart, $monthEnd])
                      ->where('actual_date', '<=', $today);
                })->orWhere(function($q) use ($monthStart, $monthEnd, $today) {
                    // Done transactions without actual_date but transaction_date in this month and <= today
                    $q->whereNull('actual_date')
                      ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                      ->where('transaction_date', '<=', $today);
                });
            })
            ->sum('amount');

        $revenueAsset = $project->transactions()
            ->where('status', 'done')
            ->where('financial_type', 'revenue')
            ->where('serving', 'asset')
            ->where(function($query) use ($monthStart, $monthEnd, $today) {
                $query->where(function($q) use ($monthStart, $monthEnd, $today) {
                    $q->whereNotNull('actual_date')
                      ->whereBetween('actual_date', [$monthStart, $monthEnd])
                      ->where('actual_date', '<=', $today);
                })->orWhere(function($q) use ($monthStart, $monthEnd, $today) {
                    $q->whereNull('actual_date')
                      ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                      ->where('transaction_date', '<=', $today);
                });
            })
            ->sum('amount');

        // Get value correction
        $valueCorrection = ValueCorrection::getCorrectionForMonth($project->key, $month);

        // Check if project is exited
        $isAfterExit = false;
        if ($project->status === 'exited' && $project->exit_date) {
            $exitMonth = Carbon::parse($project->exit_date)->format('Y-m');
            $isAfterExit = $month >= $exitMonth;
        }

        // Calculate asset evaluation using the exact formula
        $assetEvaluation = $isAfterExit ? 0 : ($previousEvaluation + $expenseAsset - $revenueAsset + $valueCorrection);

        // Store the evaluation
        MonthlyProjectEvaluation::updateOrCreate(
            [
                'project_key' => $project->key,
                'month_date' => $month,
            ],
            [
                'asset_evaluation' => $assetEvaluation,
                'expense_asset' => $expenseAsset,
                'revenue_asset' => $revenueAsset,
                'value_correction' => $valueCorrection,
                'previous_evaluation' => $previousEvaluation,
                'is_after_exit' => $isAfterExit,
            ]
        );

        return [
            'asset_evaluation' => $assetEvaluation,
        ];
    }
}
