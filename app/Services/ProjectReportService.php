<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProjectReportService
{
    private const CACHE_TTL = 30;

    public function generateMonthlyReport(array $filters = []): array
    {
        $cacheKey = 'project_report_' . md5(serialize($filters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            $startDate = $this->getStartDate($filters);
            $endDate = $this->getEndDate($filters);

            $months = $this->generateMonthRange($startDate, $endDate);
            $projectMetrics = $this->getProjectMetricsByMonth($startDate, $endDate, $filters);

            return [
                'months' => $months,
                'metrics' => $projectMetrics,
                'summary' => $this->calculateSummary($projectMetrics),
                'filters' => $filters,
                'period' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                ]
            ];
        });
    }

    public function getProjectMetricsByMonth(Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $months = $this->generateMonthRange($startDate, $endDate);
        $metrics = [];

        $allProjects = $this->getProjectsInDateRange($startDate, $endDate, $filters);
        $allTransactions = $this->getTransactionsInDateRange($startDate, $endDate, $filters);

        foreach ($months as $month) {
            $monthStart = Carbon::parse($month['key'] . '-01');
            $monthEnd = $monthStart->copy()->endOfMonth();

            $metrics[$month['key']] = [
                'month' => $month,
                'new_projects' => $this->getNewProjectsCount($monthStart, $monthEnd, $filters, $allProjects),
                'exited_projects' => $this->getExitedProjectsCount($monthStart, $monthEnd, $filters, $allProjects),
                'ongoing_projects' => $this->getOngoingProjectsCount($monthEnd, $filters, $allProjects),
                'total_investment' => $this->getTotalInvestment($monthStart, $monthEnd, $filters, $allTransactions),
                'revenue_generated' => $this->getRevenueGenerated($monthStart, $monthEnd, $filters, $allTransactions),
                'expense_generated' => $this->getExpenseGenerated($monthStart, $monthEnd, $filters, $allTransactions),
                'active_transactions' => $this->getActiveTransactionsCount($monthStart, $monthEnd, $filters, $allTransactions),
                'project_value' => $this->getProjectValue($monthStart, $monthEnd, $filters, $allProjects),
                'roi_percentage' => $this->getROIPercentage($monthStart, $monthEnd, $filters, $allTransactions),
            ];
        }

        return $metrics;
    }

    private function getProjectsInDateRange(Carbon $startDate, Carbon $endDate, array $filters): Collection
    {
        $query = Project::with(['developer'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('created_at', '>=', $startDate)
                    ->where('created_at', '<=', $endDate);
            })
            ->orWhere(function ($query) use ($startDate, $endDate) {
                $query->where('updated_at', '>=', $startDate)
                    ->where('updated_at', '<=', $endDate);
            });

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    private function getTransactionsInDateRange(Carbon $startDate, Carbon $endDate, array $filters): Collection
    {
        $query = ProjectTransaction::with(['project', 'project.developer'])
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);

        $this->applyTransactionFilters($query, $filters);

        return $query->get();
    }

    private function getNewProjectsCount(Carbon $monthStart, Carbon $monthEnd, array $filters, ?Collection $allProjects = null): int
    {
        $projects = $allProjects ?? Project::query();
        return $projects->where('created_at', '>=', $monthStart)->where('created_at', '<=', $monthEnd)->count();
    }

    private function getExitedProjectsCount(Carbon $monthStart, Carbon $monthEnd, array $filters, ?Collection $allProjects = null): int
    {
        $projects = $allProjects ?? Project::query();
        return $projects->where('status', 'exited')->where('updated_at', '>=', $monthStart)->where('updated_at', '<=', $monthEnd)->count();
    }

    private function getOngoingProjectsCount(Carbon $monthEnd, array $filters, ?Collection $allProjects = null): int
    {
        $projects = $allProjects ?? Project::query();
        return $projects->where('status', 'on-going')->where('created_at', '<=', $monthEnd)->count();
    }

    private function getTotalInvestment(Carbon $monthStart, Carbon $monthEnd, array $filters, ?Collection $allTransactions = null): float
    {
        $transactions = $allTransactions ?? $this->getTransactionsInDateRange($monthStart, $monthEnd, $filters);
        return $transactions->where('type', 'investment')->where('created_at', '>=', $monthStart)->where('created_at', '<=', $monthEnd)->sum('amount') ?? 0;
    }

    private function getRevenueGenerated(Carbon $monthStart, Carbon $monthEnd, array $filters, ?Collection $allTransactions = null): float
    {
        $transactions = $allTransactions ?? $this->getTransactionsInDateRange($monthStart, $monthEnd, $filters);
        return $transactions->where('financial_type', 'revenue')->where('transaction_date', '>=', $monthStart)->where('transaction_date', '<=', $monthEnd)->sum('amount');
    }

    private function getExpenseGenerated(Carbon $monthStart, Carbon $monthEnd, array $filters, ?Collection $allTransactions = null): float
    {
        $transactions = $allTransactions ?? $this->getTransactionsInDateRange($monthStart, $monthEnd, $filters);
        return $transactions->where('financial_type', 'expense')->where('transaction_date', '>=', $monthStart)->where('transaction_date', '<=', $monthEnd)->sum('amount');
    }

    private function getActiveTransactionsCount(Carbon $monthStart, Carbon $monthEnd, array $filters, ?Collection $allTransactions = null): int
    {
        $transactions = $allTransactions ?? $this->getTransactionsInDateRange($monthStart, $monthEnd, $filters);
        return $transactions->where('status', 'active')->where('created_at', '>=', $monthStart)->where('created_at', '<=', $monthEnd)->count();
    }

    private function getProjectValue(Carbon $monthStart, Carbon $monthEnd, array $filters, ?Collection $allProjects = null): float
    {
        $projects = $allProjects ?? Project::query();
        return $projects->where('created_at', '>=', $monthStart)->where('created_at', '<=', $monthEnd)->sum('total_area') ?? 0;
    }

    private function getROIPercentage(Carbon $monthStart, Carbon $monthEnd, array $filters, ?Collection $allTransactions = null): float
    {
        $investment = $this->getTotalInvestment($monthStart, $monthEnd, $filters, $allTransactions);
        $revenue = $this->getRevenueGenerated($monthStart, $monthEnd, $filters, $allTransactions);
        $expense = $this->getExpenseGenerated($monthStart, $monthEnd, $filters, $allTransactions);
        $profit = $revenue - $expense;

        if ($investment > 0) {
            return round(($profit / $investment) * 100, 2);
        }

        return 0;
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['developer_id'])) {
            $query->where('developer_id', $filters['developer_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['stage'])) {
            $query->where('stage', $filters['stage']);
        }
        if (!empty($filters['property_type'])) {
            $query->where('property_type', $filters['property_type']);
        }
        if (!empty($filters['investment_type'])) {
            $query->where('investment_type', $filters['investment_type']);
        }
        if (!empty($filters['location'])) {
            $query->where('location', 'like', '%' . $filters['location'] . '%');
        }
    }

    private function applyTransactionFilters($query, array $filters): void
    {
        if (!empty($filters['developer_id'])) {
            $query->whereHas('project', function ($q) use ($filters) {
                $q->where('developer_id', $filters['developer_id']);
            });
        }
        if (!empty($filters['transaction_type'])) {
            $query->where('type', $filters['transaction_type']);
        }
        if (!empty($filters['transaction_status'])) {
            $query->where('status', $filters['transaction_status']);
        }
    }

    private function generateMonthRange(Carbon $startDate, Carbon $endDate): array
    {
        $months = [];
        $current = $startDate->copy()->startOfMonth();

        while ($current->lte($endDate)) {
            $months[] = [
                'key' => $current->format('Y-m'),
                'label' => $current->format('M Y'),
                'full_label' => $current->format('F Y'),
                'year' => $current->year,
                'month' => $current->month,
                'days' => $current->daysInMonth,
            ];
            $current->addMonth();
        }

        return $months;
    }

    private function getStartDate(array $filters): Carbon
    {
        return Carbon::parse($filters['start_date'] ?? null)->startOfMonth() ?? Carbon::now()->subMonths(11)->startOfMonth();
    }

    private function getEndDate(array $filters): Carbon
    {
        return Carbon::parse($filters['end_date'] ?? null)->endOfMonth() ?? Carbon::now()->endOfMonth();
    }

    private function calculateSummary(array $metrics): array
    {
        $summary = [
            'total_new_projects' => 0,
            'total_exited_projects' => 0,
            'current_ongoing_projects' => 0,
            'total_investment' => 0,
            'total_revenue' => 0,
            'total_expense' => 0,
            'total_profit' => 0,
            'total_evaluation' => 0,
            'total_transactions' => 0,
            'average_roi' => 0,
            'best_month' => ['month' => '-', 'revenue' => 0],
            'worst_month' => ['month' => '-', 'revenue' => 0],
        ];

        $totalInvestment = 0;
        $totalRevenue = 0;
        $totalExpense = 0;
        $totalEvaluation = 0;

        foreach ($metrics as $data) {
            $summary['total_new_projects'] += $data['new_projects'];
            $summary['total_exited_projects'] += $data['exited_projects'];
            $summary['total_transactions'] += $data['active_transactions'];

            $totalInvestment += $data['total_investment'];
            $totalRevenue += $data['revenue_generated'];
            $totalExpense += $data['expense_generated'] ?? 0;
            $totalEvaluation += $data['project_value'] ?? 0;
        }

        $summary['total_investment'] = $totalInvestment;
        $summary['total_revenue'] = $totalRevenue;
        $summary['total_expense'] = $totalExpense;
        $summary['total_profit'] = $totalRevenue - $totalExpense;
        $summary['total_evaluation'] = $totalEvaluation;

        $latestMonth = array_key_last($metrics);
        if ($latestMonth) {
            $summary['current_ongoing_projects'] = $metrics[$latestMonth]['ongoing_projects'];
        }

        if ($totalInvestment > 0) {
            $summary['average_roi'] = round(($summary['total_profit'] / $totalInvestment) * 100, 2);
        } else {
            $summary['average_roi'] = 0;
        }

        $monthlyRevenue = array_map(fn($data) => $data['revenue_generated'], $metrics);
        if (!empty($monthlyRevenue)) {
            $bestMonthKey = array_keys($monthlyRevenue, max($monthlyRevenue))[0];
            $worstMonthKey = array_keys($monthlyRevenue, min($monthlyRevenue))[0];

            $summary['best_month'] = [
                'month' => $metrics[$bestMonthKey]['month']['full_label'],
                'revenue' => $monthlyRevenue[$bestMonthKey]
            ];

            $summary['worst_month'] = [
                'month' => $metrics[$worstMonthKey]['month']['full_label'],
                'revenue' => $monthlyRevenue[$worstMonthKey]
            ];
        }

        return $summary;
    }

    public function getFilterOptions(): array
    {
        return [
            'developers' => DB::table('developers')->select('id', 'name')->orderBy('name')->get()->toArray(),
            'statuses' => $this->getConfigurationOptions('project_statuses'),
            'stages' => $this->getConfigurationOptions('project_stages'),
            'property_types' => $this->getConfigurationOptions('property_types'),
            'investment_types' => $this->getConfigurationOptions('investment_types'),
            'transaction_types' => $this->getConfigurationOptions('transaction_types'),
            'transaction_statuses' => $this->getConfigurationOptions('transaction_statuses'),
        ];
    }

    private function getConfigurationOptions(string $category): array
    {
        return DB::table('system_configurations')
            ->where('category', $category)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->pluck('label', 'key')
            ->toArray();
    }

    public function clearCache(): void
    {
        $keys = Cache::getRedis()->keys('*project_report_*');
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }
    }

    /**
     * Export report data to array for CSV/Excel
     */
    public function exportReport(array $filters = []): array
    {
        $reportData = $this->generateMonthlyReport($filters);
        $exportData = [];

        // Header row
        $exportData[] = [
            'Month',
            'New Projects',
            'Exited Projects',
            'Ongoing Projects',
            'Total Investment',
            'Revenue Generated',
            'Expense Generated',
            'Profit',
            'Active Transactions',
            'ROI %'
        ];

        // Data rows
        foreach ($reportData['metrics'] as $monthData) {
            $profit = $monthData['revenue_generated'] - $monthData['expense_generated'];
            $exportData[] = [
                $monthData['month']['full_label'],
                $monthData['new_projects'],
                $monthData['exited_projects'],
                $monthData['ongoing_projects'],
                number_format($monthData['total_investment'], 2),
                number_format($monthData['revenue_generated'], 2),
                number_format($monthData['expense_generated'], 2),
                number_format($profit, 2),
                $monthData['active_transactions'],
                $monthData['roi_percentage'] . '%'
            ];
        }

        return $exportData;
    }
}
