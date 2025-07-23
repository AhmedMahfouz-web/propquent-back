<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\UserTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class UserTransactionRepository
{
    /**
     * Get base query for user transactions.
     */
    public function getBaseQuery(): Builder
    {
        return UserTransaction::query();
    }

    /**
     * Get user transactions with optional filtering.
     */
    public function getUserTransactions(int $userId = null, array $filters = []): Collection
    {
        $query = $this->getBaseQuery();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if (isset($filters['transaction_type'])) {
            $query->where('transaction_type', $filters['transaction_type']);
        }

        if (isset($filters['is_investment'])) {
            $query->where('is_investment', $filters['is_investment']);
        }

        if (isset($filters['date_range'])) {
            $query->whereBetween('transaction_date', [
                $filters['date_range']['from'],
                $filters['date_range']['until'],
            ]);
        }

        if (isset($filters['year_month'])) {
            $query->forYearMonth($filters['year_month']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }

    /**
     * Calculate monthly deposits, withdrawals, and net deposits.
     */
    public function calculateMonthlyData(int $userId = null, array $filters = []): Collection
    {
        $cacheKey = 'monthly_data_' . ($userId ?? 'all') . '_' . md5(serialize($filters));

        return Cache::remember($cacheKey, 3600, function () use ($userId, $filters) {
            // Remove global scope for aggregation queries
            $query = UserTransaction::withoutGlobalScope('latest_first');

            if ($userId) {
                $query->where('user_id', $userId);
            }

            if (isset($filters['is_investment'])) {
                $query->where('is_investment', $filters['is_investment']);
            }

            if (isset($filters['date_range'])) {
                $query->whereBetween('transaction_date', [
                    $filters['date_range']['from'],
                    $filters['date_range']['until'],
                ]);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            // Group by year-month and calculate totals
            $result = $query->select([
                DB::raw('DATE_FORMAT(transaction_date, "%Y-%m") as year_month'),
                DB::raw('SUM(CASE WHEN transaction_type = "deposit" THEN amount ELSE 0 END) as total_deposits'),
                DB::raw('SUM(CASE WHEN transaction_type = "withdrawal" THEN amount ELSE 0 END) as total_withdrawals'),
                DB::raw('SUM(CASE WHEN transaction_type = "deposit" THEN amount ELSE -amount END) as net_deposits'),
            ])
                ->groupBy(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m")'))
                ->orderBy(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m")'), 'ASC')
                ->get();

            return $result;
        });
    }

    /**
     * Determine if a user is an active investor.
     */
    public function isActiveInvestor(int $userId): bool
    {
        $cacheKey = 'active_investor_' . $userId;

        return Cache::remember($cacheKey, 3600, function () use ($userId) {
            $monthlyData = $this->calculateMonthlyData($userId, ['is_investment' => true]);

            if ($monthlyData->isEmpty()) {
                return false;
            }

            $cumulativeNetDeposit = 0;

            foreach ($monthlyData as $data) {
                $cumulativeNetDeposit += $data->net_deposits;
            }

            return $cumulativeNetDeposit > 0;
        });
    }

    /**
     * Get total monthly deposits for all users.
     */
    public function getTotalMonthlyDeposits(string $yearMonth): float
    {
        $cacheKey = 'total_monthly_deposits_' . $yearMonth;

        return Cache::remember($cacheKey, 3600, function () use ($yearMonth) {
            return $this->getBaseQuery()
                ->where('transaction_type', UserTransaction::TYPE_DEPOSIT)
                ->forYearMonth($yearMonth)
                ->sum('amount');
        });
    }

    /**
     * Get total monthly withdrawals for all users.
     */
    public function getTotalMonthlyWithdrawals(string $yearMonth): float
    {
        $cacheKey = 'total_monthly_withdrawals_' . $yearMonth;

        return Cache::remember($cacheKey, 3600, function () use ($yearMonth) {
            return $this->getBaseQuery()
                ->where('transaction_type', UserTransaction::TYPE_WITHDRAWAL)
                ->forYearMonth($yearMonth)
                ->sum('amount');
        });
    }

    /**
     * Get net monthly deposits for all users.
     */
    public function getNetMonthlyDeposits(string $yearMonth): float
    {
        return $this->getTotalMonthlyDeposits($yearMonth) - $this->getTotalMonthlyWithdrawals($yearMonth);
    }

    /**
     * Get active investors count.
     */
    public function getActiveInvestorsCount(): int
    {
        $cacheKey = 'active_investors_count';

        return Cache::remember($cacheKey, 3600, function () {
            $users = User::has('transactions')->get();
            $activeCount = 0;

            foreach ($users as $user) {
                if ($this->isActiveInvestor($user->id)) {
                    $activeCount++;
                }
            }

            return $activeCount;
        });
    }

    /**
     * Get monthly data for all months in the given range.
     */
    public function getMonthlyDataForRange(Carbon $startDate, Carbon $endDate, int $userId = null): Collection
    {
        $months = [];
        $current = $startDate->copy()->startOfMonth();
        $end = $endDate->copy()->endOfMonth();

        while ($current->lte($end)) {
            $months[] = $current->format('Y-m');
            $current->addMonth();
        }

        $result = collect();

        foreach ($months as $yearMonth) {
            $filters = ['year_month' => $yearMonth];

            if ($userId) {
                $monthlyData = $this->calculateMonthlyData($userId, $filters);
            } else {
                $monthlyData = $this->calculateMonthlyData(null, $filters);
            }

            $data = $monthlyData->first();

            if (!$data) {
                $result->push([
                    'year_month' => $yearMonth,
                    'total_deposits' => 0,
                    'total_withdrawals' => 0,
                    'net_deposits' => 0,
                ]);
            } else {
                $result->push($data);
            }
        }

        return $result;
    }
}
