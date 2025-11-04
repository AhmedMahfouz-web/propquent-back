<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MonthlyProjectEvaluation;
use App\Models\ProjectTransaction;
use App\Models\ValueCorrection;
use Carbon\Carbon;

class CleanupFutureEvaluations extends Command
{
    protected $signature = 'evaluations:cleanup-future
                          {--dry-run : Show what would be deleted without actually deleting}
                          {--years=0 : How many years in future to allow (default: 0)}';

    protected $description = 'Clean up evaluations and find transactions with dates too far in the future';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $yearsAllowed = (int) $this->option('years');

        $cutoffDate = Carbon::now()->addYears($yearsAllowed)->format('Y-m-d');

        $this->info('===========================================');
        $this->info('  CLEANUP FUTURE EVALUATIONS');
        $this->info('===========================================');
        $this->newLine();

        $this->info("Cutoff Date: {$cutoffDate}");
        $this->info("Mode: " . ($dryRun ? 'DRY RUN (no changes)' : 'LIVE (will delete)'));
        $this->newLine();

        // Find future evaluations
        $futureEvaluations = MonthlyProjectEvaluation::where('month_date', '>', $cutoffDate)
            ->orderBy('month_date', 'asc')
            ->get();

        if ($futureEvaluations->count() > 0) {
            $this->warn("Found {$futureEvaluations->count()} evaluations beyond {$cutoffDate}:");
            $this->newLine();

            $this->table(
                ['Project Key', 'Month', 'Asset Eval', 'Expense', 'Revenue'],
                $futureEvaluations->map(function ($eval) {
                    return [
                        $eval->project_key,
                        $eval->month_date,
                        '$' . number_format($eval->asset_evaluation, 2),
                        '$' . number_format($eval->expense_asset, 2),
                        '$' . number_format($eval->revenue_asset, 2),
                    ];
                })->toArray()
            );

            if (!$dryRun) {
                if ($this->confirm('Delete these future evaluations?', true)) {
                    $deleted = MonthlyProjectEvaluation::where('month_date', '>', $cutoffDate)->delete();
                    $this->info("✓ Deleted {$deleted} future evaluations");
                } else {
                    $this->info('Skipped deletion');
                }
            } else {
                $this->info('[DRY RUN] Would delete these evaluations');
            }
        } else {
            $this->info("✓ No future evaluations found beyond {$cutoffDate}");
        }

        $this->newLine();

        // Find future transactions
        $futureTransactions = ProjectTransaction::where('transaction_date', '>', $cutoffDate)
            ->orderBy('transaction_date', 'asc')
            ->get();

        if ($futureTransactions->count() > 0) {
            $this->warn("Found {$futureTransactions->count()} transactions beyond {$cutoffDate}:");
            $this->newLine();

            $this->table(
                ['ID', 'Project Key', 'Date', 'Type', 'Amount', 'Status'],
                $futureTransactions->map(function ($trans) {
                    return [
                        $trans->id,
                        $trans->project_key,
                        $trans->transaction_date,
                        $trans->financial_type,
                        '$' . number_format($trans->amount, 2),
                        $trans->status,
                    ];
                })->toArray()
            );

            $this->warn("⚠️  These transactions should be reviewed manually!");
            $this->info("   They may be legitimate future transactions or data entry errors.");
            $this->info("   To fix: Update dates in admin panel or delete if wrong.");
        } else {
            $this->info("✓ No future transactions found beyond {$cutoffDate}");
        }

        $this->newLine();

        // Find future value corrections
        $futureCorrections = ValueCorrection::where('correction_date', '>', $cutoffDate)
            ->orderBy('correction_date', 'asc')
            ->get();

        if ($futureCorrections->count() > 0) {
            $this->warn("Found {$futureCorrections->count()} value corrections beyond {$cutoffDate}:");
            $this->newLine();

            $this->table(
                ['ID', 'Project Key', 'Date', 'Amount'],
                $futureCorrections->map(function ($corr) {
                    return [
                        $corr->id,
                        $corr->project_key,
                        $corr->correction_date,
                        '$' . number_format($corr->correction_amount, 2),
                    ];
                })->toArray()
            );

            $this->warn("⚠️  These corrections should be reviewed manually!");
            $this->info("   To fix: Update dates in admin panel or delete if wrong.");
        } else {
            $this->info("✓ No future corrections found beyond {$cutoffDate}");
        }

        $this->newLine();
        $this->info('===========================================');
        $this->info('  CLEANUP COMPLETE');
        $this->info('===========================================');
        $this->newLine();

        if ($futureTransactions->count() > 0 || $futureCorrections->count() > 0) {
            $this->info('Next Steps:');
            $this->line('  1. Review and fix future transactions/corrections in admin panel');
            $this->line('  2. Run: php artisan evaluations:calculate --force');
            $this->line('  3. Verify reports show correct data');
        }

        return 0;
    }
}
