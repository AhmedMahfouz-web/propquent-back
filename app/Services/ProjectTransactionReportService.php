<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectTransaction;
use App\Models\TransactionWhat;
use App\Repositories\ProjectTransactionRepository;
use App\Repositories\ReportCacheRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProjectTransactionReportService
{
    protected $projectTransactionRepository;
    protected $reportCacheRepository;

    public function __construct(
        ProjectTransactionRepository $projectTransactionRepository,
        ReportCacheRepository $reportCacheRepository
    ) {
        $this->projectTransactionRepository = $projectTransactionRepository;
        $this->reportCacheRepository = $reportCacheRepository;
    }

    /**
     * Generate project transaction report with revenue and expense categories.
     */
    public function generateProjectTransactionReport(array $filters = []): array
    {
        $reportKey = md5(serialize($filters));
        $cachedReport = $this->reportCacheRepository->getCachedReport('project_transaction', $reportKey);

        if ($cachedReport) {
            return $cachedReport;
        }

        $projectId = $filters['project_id'] ?? null;
        $dateRange = $filters['date_range'] ?? [
            'from' => Carbon::now()->startOfYear()->subYear()->format('Y-m-d'),
            'until' => Carbon::now()->format('Y-m-d'),
        ];

        $startDate = Carbon::parse($dateRange['from']);
        $endDate = Carbon::parse($dateRange['until']);

        // Get monthly data
        $monthlyData = $this->projectTransactionRepository->getMonthlyDataForRange($startDate, $endDate, $projectId);

        // Format monthly data
        $formattedMonthlyData = $monthlyData->map(function ($item) {
            return [
                'year_month' => $item['year_month'],
                'month_name' => Carbon::createFromFormat('Y-m', $item['year_month'])->format('M Y'),
                'total_revenue' => $item['total_revenue'],
                'total_expenses' => $item['total_expenses'],
                'net_cash_flow' => $item['net_cash_flow'],
            ];
        });

        // Get category totals
        $categoryTotals = $this->getCategoryTotals($projectId, $dateRange);

        // Calculate summary
        $totalRevenue = $monthlyData->sum('total_revenue');
        $totalExpenses = $monthlyData->sum('total_expenses');
        $netCashFlow = $totalRevenue - $totalExpenses;

        $report = [
            'monthly_data' => $formattedMonthlyData,
            'category_totals' => $categoryTotals,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_expenses' => $totalExpenses,
                'net_cash_flow' => $netCashFlow,
            ],
            'filters' => $filters,
            'generated_at' => Carbon::now()->toDateTimeString(),
        ];

        // Add project details if a specific project is selected
        if ($projectId) {
            $project = Project::find($projectId);
            if ($project) {
                $report['project'] = [
                    'id' => $project->id,
                    'key' => $project->key,
                    'title' => $project->title,
                ];
            }
        }

        // Cache the report
        $this->reportCacheRepository->cacheReport('project_transaction', $reportKey, $report, 60);

        return $report;
    }

    /**
     * Get category totals for revenue and expenses.
     */
    private function getCategoryTotals(int $projectId = null, array $dateRange = []): array
    {
        $filters = [];

        if (!empty($dateRange)) {
            $filters['date_range'] = $dateRange;
        }

        // Get revenue by category
        $revenueByCategory = $this->projectTransactionRepository
            ->getMonthlyRevenueByCategory($projectId)
            ->pluck('total_amount', 'transaction_category')
            ->toArray();

        // Get expenses by category
        $expensesByCategory = $this->projectTransactionRepository
            ->getMonthlyExpensesByCategory($projectId)
            ->pluck('total_amount', 'transaction_category')
            ->toArray();

        return [
            'revenue_categories' => $revenueByCategory,
            'expense_categories' => $expensesByCategory,
        ];
    }

    /**
     * Calculate monthly revenue trends.
     */
    public function calculateMonthlyRevenueTrends(int $months = 12, int $projectId = null): array
    {
        $reportKey = "monthly_revenue_trends_{$months}_" . ($projectId ?? 'all');
        $cachedReport = $this->reportCacheRepository->getCachedReport('project_transaction', $reportKey);

        if ($cachedReport) {
            return $cachedReport;
        }

        $endDate = Carbon::now();
        $startDate = Carbon::now()->subMonths($months);

        $monthlyData = $this->projectTransactionRepository->getMonthlyDataForRange($startDate, $endDate, $projectId);

        $trends = [
            'months' => $monthlyData->pluck('month_name')->toArray(),
            'revenue' => $monthlyData->pluck('total_revenue')->toArray(),
            'expenses' => $monthlyData->pluck('total_expenses')->toArray(),
            'net_cash_flow' => $monthlyData->pluck('net_cash_flow')->toArray(),
        ];

        // Cache the report
        $this->reportCacheRepository->cacheReport('project_transaction', $reportKey, $trends, 60);

        return $trends;
    }

    /**
     * Get revenue categories.
     */
    public function getRevenueCategories(): Collection
    {
        $reportKey = "revenue_categories";
        $cachedReport = $this->reportCacheRepository->getCachedReport('project_transaction', $reportKey);

        if ($cachedReport) {
            return collect($cachedReport);
        }

        $categories = TransactionWhat::where('financial_type', ProjectTransaction::FINANCIAL_TYPE_REVENUE)
            ->select('category')
            ->distinct()
            ->pluck('category');

        // Cache the report
        $this->reportCacheRepository->cacheReport('project_transaction', $reportKey, $categories->toArray(), 60);

        return $categories;
    }

    /**
     * Get expense categories.
     */
    public function getExpenseCategories(): Collection
    {
        $reportKey = "expense_categories";
        $cachedReport = $this->reportCacheRepository->getCachedReport('project_transaction', $reportKey);

        if ($cachedReport) {
            return collect($cachedReport);
        }

        $categories = TransactionWhat::where('financial_type', ProjectTransaction::FINANCIAL_TYPE_EXPENSE)
            ->select('category')
            ->distinct()
            ->pluck('category');

        // Cache the report
        $this->reportCacheRepository->cacheReport('project_transaction', $reportKey, $categories->toArray(), 60);

        return $categories;
    }

    /**
     * Get project financial summary.
     */
    public function getProjectFinancialSummary(int $projectId, array $dateRange = []): array
    {
        return $this->projectTransactionRepository->getProjectFinancialSummary($projectId, $dateRange);
    }
}
