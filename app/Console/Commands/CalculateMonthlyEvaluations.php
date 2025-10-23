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

        // Generate all months in range
        $months = $this->generateMonthRange($dateRange['start'], $dateRange['end']);

        // Calculate evaluations month by month in chronological order
        $previousEvaluation = 0;

        // Get the evaluation from the month before our range starts
        $previousMonth = Carbon::parse($months[0])->subMonth()->format('Y-m-01');
        $previousEvaluation = MonthlyProjectEvaluation::where('project_key', $project->key)
            ->where('month_date', $previousMonth)
            ->value('asset_evaluation') ?? 0;

        // Delete existing evaluations if force is enabled (after getting previous month value)
        if ($force) {
            MonthlyProjectEvaluation::where('project_key', $project->key)
                ->whereBetween('month_date', [$dateRange['start'], $dateRange['end']])
                ->delete();
        }

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
        
        // End date should include future months if there are value corrections or future transactions
        if ($toMonth) {
            $endDate = Carbon::parse($toMonth);
        } else {
            // Get the latest date from either transactions or value corrections
            $latestValueCorrection = \App\Models\ValueCorrection::where('project_key', $project->key)
                ->orderBy('correction_date', 'desc')
                ->first();
            
            $transactionEndDate = $latestTransaction ? Carbon::parse($latestTransaction->transaction_date) : Carbon::now();
            $correctionEndDate = $latestValueCorrection ? Carbon::parse($latestValueCorrection->correction_date) : Carbon::now();
            
            // Use the later of the two dates, but at least current month
            $endDate = collect([$transactionEndDate, $correctionEndDate, Carbon::now()])->max()->startOfMonth();
        }

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

        // Get transactions for this month
        $monthStart = Carbon::parse($month)->startOfMonth();
        $monthEnd = Carbon::parse($month)->endOfMonth();
        $today = now()->startOfDay();

        // Get asset and operation transactions for this month
        $expenseAsset = 0;
        $revenueAsset = 0;
        $expenseOperation = 0;
        $revenueOperation = 0;

        foreach ($project->transactions as $transaction) {
            if ($transaction->status !== 'done') {
                continue;
            }

            // Determine which date to use - same logic as ProjectFinancialReport
            $dateToUse = null;
            $transactionDate = \Carbon\Carbon::parse($transaction->transaction_date)->startOfDay();
            $actualDate = $transaction->actual_date ? \Carbon\Carbon::parse($transaction->actual_date)->startOfDay() : null;

            if ($actualDate && $actualDate->lte($today)) {
                // Use actual_date if it exists and is <= today
                $dateToUse = $transaction->actual_date;
            } elseif (!$actualDate && $transactionDate->lte($today)) {
                // Use transaction_date if no actual_date and transaction_date <= today
                $dateToUse = $transaction->transaction_date;
            } else {
                // Skip future transactions
                continue;
            }

            // Check if this transaction belongs to the current month
            $transactionMonth = date('Y-m-01', strtotime($dateToUse));

            if ($transactionMonth === $month) {
                if ($transaction->serving === 'asset') {
                    if ($transaction->financial_type === 'expense') {
                        $expenseAsset += $transaction->amount;
                    } elseif ($transaction->financial_type === 'revenue') {
                        $revenueAsset += $transaction->amount;
                    }
                } elseif ($transaction->serving === 'operation') {
                    if ($transaction->financial_type === 'expense') {
                        $expenseOperation += $transaction->amount;
                    } elseif ($transaction->financial_type === 'revenue') {
                        $revenueOperation += $transaction->amount;
                    }
                }
            }
        }

        // Get value correction
        $valueCorrection = ValueCorrection::getCorrectionForMonth($project->key, $month);

        // Check if project is exited
        $isAfterExit = false;
        if ($project->status === 'exited' && $project->exit_date) {
            $exitMonth = Carbon::parse($project->exit_date)->format('Y-m');
            $isAfterExit = $month >= $exitMonth;
        }

        // Calculate asset evaluation using the correct formula:
        // Asset Evaluation = Previous Month + Asset Expense + Value Correction - Asset Revenue
        $assetEvaluation = $isAfterExit ? 0 : ($previousEvaluation + $expenseAsset + $valueCorrection - $revenueAsset);

        // Calculate operation profit for this month
        $profitOperation = $revenueOperation - $expenseOperation;

        // Get cumulative profits from previous month
        $previousMonth = Carbon::parse($month)->subMonth()->format('Y-m-01');
        $previousData = MonthlyProjectEvaluation::where('project_key', $project->key)
            ->where('month_date', $previousMonth)
            ->first();

        $previousAssetEvaluation = $previousData ? $previousData->asset_evaluation : 0;
        $previousProfitAssetCumulative = $previousData ? $previousData->profit_asset_cumulative : 0;
        $previousProfitOperationCumulative = $previousData ? $previousData->profit_operation_cumulative : 0;

        // Calculate monthly asset profit using your formula:
        // Monthly Asset Profit = Current Asset Evaluation - Previous Asset Evaluation + Revenue Asset - Expense Asset
        $monthlyAssetProfit = $assetEvaluation - $previousAssetEvaluation + $revenueAsset - $expenseAsset;

        // Calculate cumulative profits
        $profitAssetCumulative = $previousProfitAssetCumulative + $monthlyAssetProfit;
        $profitOperationCumulative = $previousProfitOperationCumulative + $profitOperation;
        $totalProfitCumulative = $profitAssetCumulative + $profitOperationCumulative;

        // Calculate totals
        $expenseTotal = $expenseAsset + $expenseOperation;
        $revenueTotal = $revenueAsset + $revenueOperation;

        // Store the evaluation
        $evaluation = MonthlyProjectEvaluation::updateOrCreate(
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
                'expense_operation' => $expenseOperation,
                'revenue_operation' => $revenueOperation,
                'profit_operation' => $profitOperation,
                'profit_asset_cumulative' => $profitAssetCumulative,
                'profit_operation_cumulative' => $profitOperationCumulative,
                'total_profit_cumulative' => $totalProfitCumulative,
                'expense_total' => $expenseTotal,
                'revenue_total' => $revenueTotal,
            ]
        );

        // Debug output
        $this->line("    Month {$month}: {$previousEvaluation} + {$expenseAsset} + {$valueCorrection} - {$revenueAsset} = {$assetEvaluation}");

        return [
            'asset_evaluation' => $assetEvaluation,
        ];
    }
}
