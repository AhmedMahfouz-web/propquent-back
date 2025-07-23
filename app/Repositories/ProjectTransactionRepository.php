<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\ProjectTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProjectTransactionRepository
{
    public function getBaseQuery(): Builder
    {
        return ProjectTransaction::query();
    }

    public function getProjectTransactions(int $projectId = null, array $filters = []): Collection
    {
        $query = $this->getBaseQuery();

        if ($projectId) {
            $projectKey = Project::where('id', $projectId)->value('key');
            if ($projectKey) {
                $query->where('project_key', $projectKey);
            }
        }

        if (isset($filters['financial_type'])) {
            $query->where('financial_type', $filters['financial_type']);
        }

        if (isset($filters['transaction_category'])) {
            $query->where('transaction_category', $filters['transaction_category']);
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

    public function getMonthlyRevenueByCategory(int $projectId = null, string $yearMonth = null): Collection
    {
        $cacheKey = 'monthly_revenue_' . ($projectId ?? 'all') . '_' . ($yearMonth ?? 'all');

        return Cache::remember($cacheKey, 3600, function () use ($projectId, $yearMonth) {
            $query = $this->getBaseQuery()->where('financial_type', ProjectTransaction::FINANCIAL_TYPE_REVENUE);

            if ($projectId) {
                $projectKey = Project::where('id', $projectId)->value('key');
                if ($projectKey) {
                    $query->where('project_key', $projectKey);
                }
            }

            if ($yearMonth) {
                $query->forYearMonth($yearMonth);
            }

            return $query->select(
                'transaction_category',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as transaction_count')
            )
                ->groupBy('transaction_category')
                ->get();
        });
    }

    public function getMonthlyExpensesByCategory(int $projectId = null, string $yearMonth = null): Collection
    {
        $cacheKey = 'monthly_expenses_' . ($projectId ?? 'all') . '_' . ($yearMonth ?? 'all');

        return Cache::remember($cacheKey, 3600, function () use ($projectId, $yearMonth) {
            $query = $this->getBaseQuery()->where('financial_type', ProjectTransaction::FINANCIAL_TYPE_EXPENSE);

            if ($projectId) {
                $projectKey = Project::where('id', $projectId)->value('key');
                if ($projectKey) {
                    $query->where('project_key', $projectKey);
                }
            }

            if ($yearMonth) {
                $query->forYearMonth($yearMonth);
            }

            return $query->select(
                'transaction_category',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as transaction_count')
            )
                ->groupBy('transaction_category')
                ->get();
        });
    }

    public function calculateMonthlyNetCashFlow(int $projectId = null, string $yearMonth = null): float
    {
        $cacheKey = 'monthly_net_cash_flow_' . ($projectId ?? 'all') . '_' . ($yearMonth ?? 'all');

        return Cache::remember($cacheKey, 3600, function () use ($projectId, $yearMonth) {
            $query = $this->getBaseQuery();

            if ($projectId) {
                $projectKey = Project::where('id', $projectId)->value('key');
                if ($projectKey) {
                    $query->where('project_key', $projectKey);
                }
            }

            if ($yearMonth) {
                $query->forYearMonth($yearMonth);
            }

            $totalRevenue = (clone $query)->where('financial_type', ProjectTransaction::FINANCIAL_TYPE_REVENUE)->sum('amount');
            $totalExpenses = (clone $query)->where('financial_type', ProjectTransaction::FINANCIAL_TYPE_EXPENSE)->sum('amount');

            return $totalRevenue - $totalExpenses;
        });
    }

    public function getProjectFinancialSummary(int $projectId = null, array $dateRange = []): array
    {
        $cacheKey = 'project_financial_summary_' . ($projectId ?? 'all') . '_' . md5(serialize($dateRange));

        return Cache::remember($cacheKey, 3600, function () use ($projectId, $dateRange) {
            $query = $this->getBaseQuery();

            if ($projectId) {
                $projectKey = Project::where('id', $projectId)->value('key');
                if ($projectKey) {
                    $query->where('project_key', $projectKey);
                }
            }

            if (!empty($dateRange)) {
                $query->whereBetween('transaction_date', [$dateRange['from'], $dateRange['until']]);
            }

            $totalRevenue = (clone $query)->where('financial_type', ProjectTransaction::FINANCIAL_TYPE_REVENUE)->sum('amount');
            $totalExpenses = (clone $query)->where('financial_type', ProjectTransaction::FINANCIAL_TYPE_EXPENSE)->sum('amount');

            $revenueByCategory = (clone $query)->where('financial_type', ProjectTransaction::FINANCIAL_TYPE_REVENUE)
                ->select('transaction_category', DB::raw('SUM(amount) as total_amount'))
                ->groupBy('transaction_category')
                ->pluck('total_amount', 'transaction_category')
                ->toArray();

            $expensesByCategory = (clone $query)->where('financial_type', ProjectTransaction::FINANCIAL_TYPE_EXPENSE)
                ->select('transaction_category', DB::raw('SUM(amount) as total_amount'))
                ->groupBy('transaction_category')
                ->pluck('total_amount', 'transaction_category')
                ->toArray();

            return [
                'total_revenue' => $totalRevenue,
                'total_expenses' => $totalExpenses,
                'net_cash_flow' => $totalRevenue - $totalExpenses,
                'revenue_by_category' => $revenueByCategory,
                'expenses_by_category' => $expensesByCategory,
            ];
        });
    }

    public function getMonthlyDataForRange(Carbon $startDate, Carbon $endDate, int $projectId = null): Collection
    {
        $query = $this->getBaseQuery()
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m') as year_month"),
                DB::raw("SUM(CASE WHEN financial_type = 'revenue' THEN amount ELSE 0 END) as total_revenue"),
                DB::raw("SUM(CASE WHEN financial_type = 'expense' THEN amount ELSE 0 END) as total_expenses")
            )
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->groupBy('year_month')
            ->orderBy('year_month', 'asc');

        if ($projectId) {
            $projectKey = Project::where('id', $projectId)->value('key');
            if ($projectKey) {
                $query->where('project_key', $projectKey);
            }
        }

        $monthlyData = $query->get()->keyBy('year_month');

        $result = collect();
        $current = $startDate->copy()->startOfMonth();
        $end = $endDate->copy()->endOfMonth();

        while ($current->lte($end)) {
            $yearMonth = $current->format('Y-m');
            $data = $monthlyData->get($yearMonth);

            $revenue = $data ? (float) $data->total_revenue : 0;
            $expenses = $data ? (float) $data->total_expenses : 0;

            $result->push([
                'year_month' => $yearMonth,
                'total_revenue' => $revenue,
                'total_expenses' => $expenses,
                'net_cash_flow' => $revenue - $expenses,
            ]);

            $current->addMonth();
        }

        return $result;
    }
}
