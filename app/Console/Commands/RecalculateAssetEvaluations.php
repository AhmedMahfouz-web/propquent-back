<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\ProjectTransaction;
use App\Models\ValueCorrection;
use App\Models\MonthlyProjectEvaluation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecalculateAssetEvaluations extends Command
{
    protected $signature = 'evaluations:recalculate 
                          {--project-key= : Calculate for specific project only}
                          {--from-month= : Start from specific month (Y-m-01 format)}
                          {--to-month= : End at specific month (Y-m-01 format)}
                          {--clear : Clear all existing evaluations before recalculating}';

    protected $description = 'Recalculate monthly project asset evaluations with improved logic';

    public function handle()
    {
        $this->info('Starting asset evaluation recalculation...');

        $projectKey = $this->option('project-key');
        $fromMonth = $this->option('from-month');
        $toMonth = $this->option('to-month');
        $clear = $this->option('clear');

        // Clear existing evaluations if requested
        if ($clear) {
            $this->info('Clearing existing evaluations...');
            if ($projectKey) {
                MonthlyProjectEvaluation::where('project_key', $projectKey)->delete();
            } else {
                MonthlyProjectEvaluation::truncate();
            }
        }

        // Get projects to process
        $projectsQuery = Project::with(['transactions' => function($query) {
            $query->where('status', 'done')
                  ->where('serving', 'asset')
                  ->orderBy('transaction_date');
        }]);
        
        if ($projectKey) {
            $projectsQuery->where('key', $projectKey);
        }
        
        $projects = $projectsQuery->get();

        if ($projects->isEmpty()) {
            $this->error('No projects found to process.');
            return 1;
        }

        $this->info("Processing {$projects->count()} projects...");

        // Process each project
        foreach ($projects as $project) {
            $this->processProject($project, $fromMonth, $toMonth);
        }

        $this->info('Asset evaluation recalculation completed!');
        return 0;
    }

    private function processProject(Project $project, ?string $fromMonth, ?string $toMonth): void
    {
        $this->line("Processing project: {$project->key} - {$project->title}");

        // Get date range for this project
        $dateRange = $this->getProjectDateRange($project, $fromMonth, $toMonth);
        
        if (empty($dateRange)) {
            $this->line("  No asset transactions found for project {$project->key}");
            return;
        }

        // Generate all months in range (chronological order)
        $months = $this->generateMonthRange($dateRange['start'], $dateRange['end']);
        
        $this->line("  Processing months: " . implode(', ', $months));

        // Start with zero evaluation
        $previousEvaluation = 0;
        
        // Get the evaluation from the month before our range starts
        if (!empty($months)) {
            $previousMonth = Carbon::parse($months[0])->subMonth()->format('Y-m-01');
            $previousEvaluation = MonthlyProjectEvaluation::where('project_key', $project->key)
                ->where('month_date', $previousMonth)
                ->value('asset_evaluation') ?? 0;
        }
        
        $this->line("  Starting with previous evaluation: {$previousEvaluation}");
        
        // Calculate evaluations month by month in chronological order
        foreach ($months as $month) {
            $evaluation = $this->calculateMonthEvaluation($project, $month, $previousEvaluation);
            $previousEvaluation = $evaluation; // Update for next month
        }

        $this->line("  Completed {$project->key}: " . count($months) . " months processed");
    }

    private function getProjectDateRange(Project $project, ?string $fromMonth, ?string $toMonth): array
    {
        // Get earliest and latest asset transaction dates
        $transactions = $project->transactions()
            ->where('status', 'done')
            ->where('serving', 'asset')
            ->selectRaw('MIN(transaction_date) as earliest, MAX(transaction_date) as latest')
            ->first();

        if (!$transactions || !$transactions->earliest) {
            return [];
        }

        $startDate = $fromMonth ? 
            Carbon::parse($fromMonth) : 
            Carbon::parse($transactions->earliest)->startOfMonth();
            
        $endDate = $toMonth ? 
            Carbon::parse($toMonth) : 
            Carbon::now()->startOfMonth();

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

    private function calculateMonthEvaluation(Project $project, string $month, float $previousEvaluation): float
    {
        $monthStart = Carbon::parse($month)->startOfMonth();
        $monthEnd = Carbon::parse($month)->endOfMonth();
        $today = now()->startOfDay();

        // Calculate expense and revenue for this month
        $expenseAsset = 0;
        $revenueAsset = 0;
        
        foreach ($project->transactions as $transaction) {
            if ($transaction->status !== 'done' || $transaction->serving !== 'asset') {
                continue;
            }
            
            // Determine which date to use
            $dateToUse = null;
            $transactionDate = Carbon::parse($transaction->transaction_date)->startOfDay();
            $actualDate = $transaction->actual_date ? Carbon::parse($transaction->actual_date)->startOfDay() : null;
            
            if ($actualDate && $actualDate->lte($today)) {
                $dateToUse = $transaction->actual_date;
            } elseif (!$actualDate && $transactionDate->lte($today)) {
                $dateToUse = $transaction->transaction_date;
            } else {
                continue; // Skip future transactions
            }
            
            // Check if this transaction belongs to the current month
            $transactionMonth = Carbon::parse($dateToUse)->format('Y-m-01');
            
            if ($transactionMonth === $month) {
                if ($transaction->financial_type === 'expense') {
                    $expenseAsset += $transaction->amount;
                } elseif ($transaction->financial_type === 'revenue') {
                    $revenueAsset += $transaction->amount;
                }
            }
        }

        // Get value correction for this month
        $valueCorrection = ValueCorrection::getCorrectionForMonth($project->key, $month);

        // Check if project is exited
        $isAfterExit = false;
        if ($project->status === 'exited' && $project->exit_date) {
            $exitMonth = Carbon::parse($project->exit_date)->format('Y-m-01');
            $isAfterExit = $month >= $exitMonth;
        }

        // Calculate asset evaluation using the formula:
        // Asset Evaluation = Previous Month + Expense Asset - Revenue Asset + Value Correction
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
        
        // Debug output
        if ($isAfterExit) {
            $this->line("    Month {$month}: EXITED PROJECT - Asset Evaluation = 0");
        } else {
            $this->line("    Month {$month}: {$previousEvaluation} + {$expenseAsset} - {$revenueAsset} + {$valueCorrection} = {$assetEvaluation}");
        }

        return $assetEvaluation;
    }
}
