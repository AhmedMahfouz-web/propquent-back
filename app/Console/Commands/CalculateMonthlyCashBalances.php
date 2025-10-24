<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\MonthlyCashBalance;
use App\Models\UserTransaction;
use Carbon\Carbon;

class CalculateMonthlyCashBalances extends Command
{
    protected $signature = 'cash:calculate 
                            {--from= : Start month (YYYY-MM-01)}
                            {--to= : End month (YYYY-MM-01)}
                            {--force : Force recalculation of existing records}';

    protected $description = 'Calculate and store monthly cash balances in database for performance';

    public function handle()
    {
        $this->info('Starting monthly cash balance calculation...');
        
        // Get date range
        $fromMonth = $this->option('from') ?: $this->getFirstTransactionMonth();
        $toMonth = $this->option('to') ?: now()->format('Y-m-01');
        $force = $this->option('force');

        if (!$fromMonth) {
            $this->warn('No transactions found in database.');
            return;
        }

        $this->info("Calculating from {$fromMonth} to {$toMonth}");

        // Generate all months in range
        $allMonths = $this->generateMonthRange($fromMonth, $toMonth);
        
        // Get ALL project transactions grouped by month
        $projectTransactions = $this->getProjectTransactions($toMonth);
        
        // Get ALL user transactions grouped by month
        $userTransactions = $this->getUserTransactions($toMonth);

        // Calculate cumulative cash for each month
        $previousCash = 0;
        $processed = 0;
        $updated = 0;

        foreach ($allMonths as $month) {
            $revenue = $projectTransactions['revenue'][$month] ?? 0;
            $expense = $projectTransactions['expense'][$month] ?? 0;
            $deposits = $userTransactions[$month]['deposits'] ?? 0;
            $withdrawals = $userTransactions[$month]['withdrawals'] ?? 0;

            $cashBalance = $previousCash + $deposits + $revenue - $withdrawals - $expense;

            // Check if record exists
            $existing = MonthlyCashBalance::where('month_date', $month)->first();

            if ($existing && !$force) {
                // Update only if values changed
                if ($existing->cash_balance != $cashBalance) {
                    $existing->update([
                        'revenue' => $revenue,
                        'expense' => $expense,
                        'deposits' => $deposits,
                        'withdrawals' => $withdrawals,
                        'cash_balance' => $cashBalance,
                        'previous_month_cash' => $previousCash,
                    ]);
                    $updated++;
                }
            } else {
                // Create or force update
                MonthlyCashBalance::updateOrCreate(
                    ['month_date' => $month],
                    [
                        'revenue' => $revenue,
                        'expense' => $expense,
                        'deposits' => $deposits,
                        'withdrawals' => $withdrawals,
                        'cash_balance' => $cashBalance,
                        'previous_month_cash' => $previousCash,
                    ]
                );
                $processed++;
            }

            $previousCash = $cashBalance;
        }

        $this->info("✓ Processed {$processed} months");
        if ($updated > 0) {
            $this->info("✓ Updated {$updated} existing months");
        }
        $this->info('Cash balance calculation completed successfully!');
    }

    private function getFirstTransactionMonth(): ?string
    {
        // Get earliest transaction from both project and user transactions
        $projectMin = DB::table('project_transactions')
            ->selectRaw("MIN(DATE_FORMAT(transaction_date, '%Y-%m-01')) as min_month")
            ->value('min_month');

        $userMin = DB::table('user_transactions')
            ->selectRaw("MIN(DATE_FORMAT(transaction_date, '%Y-%m-01')) as min_month")
            ->value('min_month');

        if (!$projectMin && !$userMin) {
            return null;
        }

        if (!$projectMin) return $userMin;
        if (!$userMin) return $projectMin;

        return min($projectMin, $userMin);
    }

    private function generateMonthRange(string $from, string $to): array
    {
        $months = [];
        $current = Carbon::parse($from);
        $end = Carbon::parse($to);

        while ($current <= $end) {
            $months[] = $current->format('Y-m-01');
            $current->addMonth();
        }

        return $months;
    }

    private function getProjectTransactions(string $upToMonth): array
    {
        $transactions = DB::table('project_transactions')
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m-01') as month_date")
            ->selectRaw('financial_type as type')
            ->selectRaw('SUM(amount) as total_amount')
            ->where('transaction_date', '<=', Carbon::parse($upToMonth)->endOfMonth())
            ->groupBy('month_date', 'type')
            ->get();

        $result = ['revenue' => [], 'expense' => []];

        foreach ($transactions as $transaction) {
            $type = strtolower($transaction->type);
            if ($type === 'revenue' || $type === 'expense') {
                if (!isset($result[$type][$transaction->month_date])) {
                    $result[$type][$transaction->month_date] = 0;
                }
                $result[$type][$transaction->month_date] += $transaction->total_amount;
            }
        }

        return $result;
    }

    private function getUserTransactions(string $upToMonth): array
    {
        $transactions = DB::table('user_transactions')
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m-01') as month_date")
            ->selectRaw("SUM(CASE WHEN transaction_type = '" . UserTransaction::TYPE_DEPOSIT . "' THEN amount ELSE 0 END) as deposits")
            ->selectRaw("SUM(CASE WHEN transaction_type = '" . UserTransaction::TYPE_WITHDRAWAL . "' THEN amount ELSE 0 END) as withdrawals")
            ->where('status', UserTransaction::STATUS_DONE)
            ->where('transaction_date', '<=', Carbon::parse($upToMonth)->endOfMonth())
            ->groupBy('month_date')
            ->get();

        $result = [];

        foreach ($transactions as $transaction) {
            $result[$transaction->month_date] = [
                'deposits' => $transaction->deposits,
                'withdrawals' => $transaction->withdrawals,
            ];
        }

        return $result;
    }
}
