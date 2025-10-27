<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserTransaction;
use App\Models\ProjectTransaction;
use App\Models\MonthlyProjectEvaluation;
use App\Models\MonthlyCashBalance;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Get home dashboard data for authenticated user
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $currentDate = Carbon::now();

            // Main financial summary always uses current month (no date filtering)
            $capitalInvestment = $this->calculateCapitalInvestment($user->id, $currentDate);
            $capitalChangePercent = $this->calculateCapitalChangePercent($user->id, $currentDate);

            $totalProfit = $this->calculateTotalProfit($user->id, $currentDate);
            $thisMonthProfit = $this->calculateThisMonthProfit($user->id, $currentDate);
            $assetProfit = $this->calculateAssetProfit($user->id, $currentDate);
            $operationProfit = $this->calculateOperationProfit($user->id, $currentDate);

            $roi = $this->calculateROI($user->id, $currentDate);

            $depositData = $this->calculateDepositData($user->id);

            // Historical data: Get date range from request or default to last 12 months
            $historicalEndDate = $request->has('end_date')
                ? Carbon::parse($request->end_date)
                : $currentDate;

            $historicalStartDate = $request->has('start_date')
                ? Carbon::parse($request->start_date)
                : $historicalEndDate->copy()->subMonths(12);

            // Get historical data for charts (only capital and profit)
            $historicalData = $this->getHistoricalData($user->id, $historicalStartDate, $historicalEndDate);

            // Get recent transactions
            $transactionsPerPage = $request->get('per_page', 10);
            $transactionsPage = $request->get('transactions_page', 1);
            $recentTransactions = $this->getRecentTransactions($user->id, $transactionsPerPage, $transactionsPage);

            return response()->json([
                'success' => true,
                'message' => 'Dashboard data retrieved successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'full_name' => $user->full_name,
                        'custom_id' => $user->custom_id,
                        'email' => $user->email
                    ],
                    'financial_summary' => [
                        'capital_investment' => [
                            'amount' => $capitalInvestment,
                            'change_percent' => $capitalChangePercent,
                            'currency' => 'USD'
                        ],
                        'profit' => [
                            'total' => $totalProfit,
                            'this_month' => $thisMonthProfit,
                            'asset_profit' => $assetProfit,
                            'operation_profit' => $operationProfit,
                            'currency' => 'USD'
                        ],
                        'roi' => [
                            'percentage' => $roi,
                            'description' => 'Return on Investment'
                        ],
                        'deposits_withdrawals' => [
                            'net_deposit' => $depositData['net_deposit'],
                            'total_deposits' => $depositData['total_deposits'],
                            'total_withdrawals' => $depositData['total_withdrawals'],
                            'currency' => 'USD'
                        ]
                    ],
                    'historical_data' => $historicalData,
                    'recent_transactions' => $recentTransactions,
                    'date_range' => [
                        'start_date' => $historicalStartDate->format('Y-m-d'),
                        'end_date' => $historicalEndDate->format('Y-m-d'),
                        'months_count' => $historicalStartDate->diffInMonths($historicalEndDate)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate total capital investment for user
     */
    private function calculateCapitalInvestment(int $userId, Carbon $endDate): float
    {
        return UserTransaction::where('user_id', $userId)
            ->where('transaction_type', 'deposit')
            ->where('status', 'done')
            ->where('transaction_date', '<=', $endDate)
            ->sum('amount');
    }

    /**
     * Calculate capital investment change percentage from last month
     */
    private function calculateCapitalChangePercent(int $userId, Carbon $endDate): float
    {
        $currentMonthStart = $endDate->copy()->startOfMonth();
        $lastMonthStart = $endDate->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $endDate->copy()->subMonth()->endOfMonth();

        $currentCapital = $this->calculateCapitalInvestment($userId, $endDate);
        $lastMonthCapital = $this->calculateCapitalInvestment($userId, $lastMonthEnd);

        if ($lastMonthCapital == 0) {
            return $currentCapital > 0 ? 100 : 0;
        }

        return round((($currentCapital - $lastMonthCapital) / $lastMonthCapital) * 100, 2);
    }

    /**
     * Calculate total profit for user (matches User Financial Report logic)
     * Formula: User Profit = (Previous Month Equity % / 100) × Current Month Company Profit
     *
     * Where Company Profit = Asset Profit + Operation Profit
     * - Asset Profit: (Current Evaluation - Previous Evaluation + Revenue - Expense)
     * - Operation Profit: (Revenue - Expense)
     *
     * IMPORTANT: Uses PREVIOUS month's equity percentage (as a fraction, not percentage)
     */
    private function calculateTotalProfit(int $userId, Carbon $endDate): float
    {
        // Get previous month's equity percentage
        $previousMonthEquityPercentage = $this->getUserEquityPercentage($userId, $endDate->copy()->subMonth());

        // Convert percentage to fraction (divide by 100)
        $equityFraction = $previousMonthEquityPercentage / 100;

        // Get current month's company profit
        $currentMonthProjectsProfit = $this->getCurrentMonthProjectsProfit($endDate);

        // User Profit = Equity Fraction × Company Profit
        return $equityFraction * $currentMonthProjectsProfit;
    }

    /**
     * Calculate this month's profit (same as total profit for current month)
     */
    private function calculateThisMonthProfit(int $userId, Carbon $endDate): float
    {
        // For current month, use same calculation as total profit
        return $this->calculateTotalProfit($userId, $endDate);
    }

    /**
     * Calculate ROI (Return on Investment)
     */
    private function calculateROI(int $userId, Carbon $endDate): float
    {
        $totalInvestment = $this->calculateCapitalInvestment($userId, $endDate);
        $totalProfit = $this->calculateTotalProfit($userId, $endDate);

        if ($totalInvestment == 0) {
            return 0;
        }

        return round(($totalProfit / $totalInvestment) * 100, 2);
    }

    /**
     * Calculate deposit/withdrawal data
     */
    private function calculateDepositData(int $userId): array
    {
        $totalDeposits = UserTransaction::where('user_id', $userId)
            ->where('transaction_type', 'deposit')
            ->where('status', 'done')
            ->sum('amount');

        $totalWithdrawals = UserTransaction::where('user_id', $userId)
            ->where('transaction_type', 'withdrawa')
            ->where('status', 'done')
            ->sum('amount');

        return [
            'total_deposits' => $totalDeposits,
            'total_withdrawals' => $totalWithdrawals,
            'net_deposit' => $totalDeposits - $totalWithdrawals
        ];
    }

    /**
     * Get historical data for capital and profit only (last 12 months or custom range)
     */
    private function getHistoricalData(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $months = [];
        $current = $startDate->copy()->startOfMonth();

        while ($current <= $endDate) {
            $monthEnd = $current->copy()->endOfMonth();
            if ($monthEnd > $endDate) {
                $monthEnd = $endDate;
            }

            // Calculate profit for this month using the same formula
            $previousMonthEquityPercentage = $this->getUserEquityPercentage($userId, $current->copy()->subMonth());
            $equityFraction = $previousMonthEquityPercentage / 100;
            $currentMonthProfit = $this->getCurrentMonthProjectsProfit($monthEnd);
            $userProfit = $equityFraction * $currentMonthProfit;

            $monthData = [
                'month' => $current->format('Y-m'),
                'month_name' => $current->format('M Y'),
                'capital_investment' => $this->calculateCapitalInvestment($userId, $monthEnd),
                'profit' => $userProfit
            ];

            $months[] = $monthData;
            $current->addMonth();
        }

        return $months;
    }

    /**
     * Get recent transactions with pagination
     */
    private function getRecentTransactions(int $userId, int $perPage = 10, int $page = 1): array
    {
        $offset = ($page - 1) * $perPage;

        $transactions = UserTransaction::where('user_id', $userId)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_type' => $transaction->transaction_type,
                    'amount' => $transaction->amount,
                    'method' => $transaction->method,
                    'status' => $transaction->status,
                    'transaction_date' => $transaction->transaction_date,
                    'reference_no' => $transaction->reference_no,
                    'note' => $transaction->note,
                    'created_at' => $transaction->created_at
                ];
            });

        $totalTransactions = UserTransaction::where('user_id', $userId)->count();
        $hasMore = ($offset + $perPage) < $totalTransactions;

        return [
            'data' => $transactions,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalTransactions,
                'has_more' => $hasMore,
                'next_page' => $hasMore ? $page + 1 : null
            ]
        ];
    }

    /**
     * Calculate user's equity percentage based on company total equity (cash + asset evaluation)
     * This matches User Financial Report calculation EXACTLY
     * Returns as PERCENTAGE (e.g., 0.78 for 0.78%), not fraction
     */
    private function getUserEquityPercentage(int $userId, Carbon $endDate): float
    {
        // Calculate user's cumulative equity up to the given date
        $userEquity = $this->calculateUserCumulativeEquity($userId, $endDate);

        // Calculate company total equity (cash + asset evaluation)
        $companyTotalEquity = $this->calculateCompanyTotalEquity($endDate);

        if ($companyTotalEquity == 0) {
            return 0;
        }

        // Return as percentage (multiply by 100)
        // e.g., if user has $150k and company has $19.2M, return 0.78 (not 0.0078)
        return ($userEquity / $companyTotalEquity) * 100;
    }

    /**
     * Calculate user's cumulative equity (deposits - withdrawals)
     */
    private function calculateUserCumulativeEquity(int $userId, Carbon $endDate): float
    {
        $deposits = UserTransaction::where('user_id', $userId)
            ->where('transaction_type', 'deposit')
            ->where('status', 'done')
            ->where('transaction_date', '<=', $endDate)
            ->sum('amount');

        $withdrawals = UserTransaction::where('user_id', $userId)
            ->where('transaction_type', 'withdrawal')
            ->where('status', 'done')
            ->where('transaction_date', '<=', $endDate)
            ->sum('amount');

        return $deposits - $withdrawals;
    }

    /**
     * Calculate company total equity (cash + asset evaluation)
     * Uses cached MonthlyCashBalance table when available, calculates manually as fallback
     */
    private function calculateCompanyTotalEquity(Carbon $endDate): float
    {
        $month = $endDate->format('Y-m-01');

        // Try to get cached cash balance first
        $cachedCash = MonthlyCashBalance::where('month_date', $month)->first();

        if ($cachedCash) {
            // Use cached cash balance
            $cash = (float) $cachedCash->cash_balance;
        } else {
            // Fallback: Calculate manually
            $allDeposits = UserTransaction::where('transaction_type', 'deposit')
                ->where('status', 'done')
                ->where('transaction_date', '<=', $endDate)
                ->sum('amount');

            $allWithdrawals = UserTransaction::where('transaction_type', 'withdrawal')
                ->where('status', 'done')
                ->where('transaction_date', '<=', $endDate)
                ->sum('amount');

            $allRevenue = ProjectTransaction::where('financial_type', 'revenue')
                ->where('status', 'done')
                ->where('transaction_date', '<=', $endDate)
                ->sum('amount');

            $allExpenses = ProjectTransaction::where('financial_type', 'expense')
                ->where('status', 'done')
                ->where('transaction_date', '<=', $endDate)
                ->sum('amount');

            $cash = $allDeposits - $allWithdrawals + $allRevenue - $allExpenses;
        }

        // Get total asset evaluation for this month from MonthlyProjectEvaluation
        $assetEvaluation = MonthlyProjectEvaluation::where('month_date', $month)
            ->sum('asset_evaluation');

        // Company Total Equity = Cash + Asset Evaluation
        return $cash + $assetEvaluation;
    }

    /**
     * Calculate current month's total projects profit (matches User Financial Report logic)
     */
    private function getCurrentMonthProjectsProfit(Carbon $endDate): float
    {
        $assetProfit = $this->calculateCurrentMonthAssetProfit($endDate);
        $operationProfit = $this->calculateCurrentMonthOperationProfit($endDate);

        return $assetProfit + $operationProfit;
    }

    /**
     * Calculate current month's asset profit (using evaluation-based formula)
     */
    private function calculateCurrentMonthAssetProfit(Carbon $endDate): float
    {
        $currentMonth = $endDate->format('Y-m-01');
        $previousMonth = $endDate->copy()->subMonth()->format('Y-m-01');

        $totalAssetProfit = 0;
        $projects = Project::all();

        foreach ($projects as $project) {
            // Get current month evaluation
            $currentEvaluation = MonthlyProjectEvaluation::where('project_key', $project->key)
                ->where('month_date', $currentMonth)
                ->first();

            // Get previous month evaluation
            $previousEvaluation = MonthlyProjectEvaluation::where('project_key', $project->key)
                ->where('month_date', $previousMonth)
                ->first();

            if ($currentEvaluation) {
                $currentAssetEval = (float) $currentEvaluation->asset_evaluation;
                $previousAssetEval = $previousEvaluation ? (float) $previousEvaluation->asset_evaluation : 0;
                $revenueAsset = (float) $currentEvaluation->revenue_asset;
                $expenseAsset = (float) $currentEvaluation->expense_asset;

                // Profit Asset Formula: Current Evaluation - Previous Evaluation + Revenue - Expense
                $profitAsset = $currentAssetEval - $previousAssetEval + $revenueAsset - $expenseAsset;
                $totalAssetProfit += $profitAsset;
            }
        }

        return $totalAssetProfit;
    }

    /**
     * Calculate current month's operation profit (simple revenue - expense)
     */
    private function calculateCurrentMonthOperationProfit(Carbon $endDate): float
    {
        $monthStart = $endDate->copy()->startOfMonth();

        // Get revenue from operation project transactions for current month
        $revenue = ProjectTransaction::where('financial_type', 'revenue')
            ->where('serving', 'operation')
            ->where('status', 'done')
            ->whereBetween('transaction_date', [$monthStart, $endDate])
            ->sum('amount');

        // Get expenses from operation project transactions for current month
        $expenses = ProjectTransaction::where('financial_type', 'expense')
            ->where('serving', 'operation')
            ->where('status', 'done')
            ->whereBetween('transaction_date', [$monthStart, $endDate])
            ->sum('amount');

        return $revenue - $expenses;
    }

    /**
     * Calculate asset profit (previous month equity % / 100 * current month asset projects profit)
     */
    private function calculateAssetProfit(int $userId, Carbon $endDate): float
    {
        $previousMonthEquityPercentage = $this->getUserEquityPercentage($userId, $endDate->copy()->subMonth());
        $equityFraction = $previousMonthEquityPercentage / 100;
        $currentMonthAssetProfit = $this->getCurrentMonthAssetProjectsProfit($endDate);

        return $equityFraction * $currentMonthAssetProfit;
    }

    /**
     * Calculate operation profit (previous month equity % / 100 * current month operation projects profit)
     */
    private function calculateOperationProfit(int $userId, Carbon $endDate): float
    {
        $previousMonthEquityPercentage = $this->getUserEquityPercentage($userId, $endDate->copy()->subMonth());
        $equityFraction = $previousMonthEquityPercentage / 100;
        $currentMonthOperationProfit = $this->getCurrentMonthOperationProjectsProfit($endDate);

        return $equityFraction * $currentMonthOperationProfit;
    }

    /**
     * Calculate current month's asset projects profit (using evaluation-based formula)
     */
    private function getCurrentMonthAssetProjectsProfit(Carbon $endDate): float
    {
        return $this->calculateCurrentMonthAssetProfit($endDate);
    }

    /**
     * Calculate current month's operation projects profit (simple revenue - expense)
     */
    private function getCurrentMonthOperationProjectsProfit(Carbon $endDate): float
    {
        return $this->calculateCurrentMonthOperationProfit($endDate);
    }

    /**
     * Get user profile with financial data and transactions (matches User Financial Report)
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $currentDate = Carbon::now();

            // Get date range from request or default to last 12 months
            $endMonth = $request->has('end_month')
                ? Carbon::parse($request->end_month)->format('Y-m-01')
                : $currentDate->format('Y-m-01');

            $startMonth = $request->has('start_month')
                ? Carbon::parse($request->start_month)->format('Y-m-01')
                : Carbon::parse($endMonth)->subMonths(11)->format('Y-m-01');

            // Get monthly financial data (matches User Financial Report structure)
            $monthlyData = $this->getUserMonthlyFinancialData($user->id, $startMonth, $endMonth);

            // Calculate current totals
            $currentEquityPercentage = $this->getUserEquityPercentage($user->id, $currentDate);
            $totalDeposits = $this->calculateTotalDeposits($user->id);
            $totalWithdrawals = $this->calculateTotalWithdrawals($user->id);
            $currentEquity = $totalDeposits - $totalWithdrawals;

            // Get current month profit
            $currentMonthProfit = $this->calculateTotalProfit($user->id, $currentDate);
            $currentAssetProfit = $this->calculateAssetProfit($user->id, $currentDate);
            $currentOperationProfit = $this->calculateOperationProfit($user->id, $currentDate);

            // Get recent transactions with pagination
            $transactionsPerPage = $request->get('per_page', 10);
            $transactionsPage = $request->get('transactions_page', 1);
            $recentTransactions = $this->getRecentTransactions($user->id, $transactionsPerPage, $transactionsPage);

            return response()->json([
                'success' => true,
                'message' => 'Profile data retrieved successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'full_name' => $user->full_name,
                        'custom_id' => $user->custom_id,
                        'email' => $user->email,
                        'phone_number' => $user->phone_number,
                        'country' => $user->country,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at
                    ],
                    'financial_summary' => [
                        'total_deposits' => $totalDeposits,
                        'total_withdrawals' => $totalWithdrawals,
                        'current_equity' => $currentEquity,
                        'equity_percentage' => round($currentEquityPercentage, 2),
                        'current_month_profit' => $currentMonthProfit,
                        'current_asset_profit' => $currentAssetProfit,
                        'current_operation_profit' => $currentOperationProfit,
                        'currency' => 'USD'
                    ],
                    'monthly_data' => $monthlyData,
                    'recent_transactions' => $recentTransactions,
                    'date_range' => [
                        'start_month' => $startMonth,
                        'end_month' => $endMonth
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's monthly financial data (matches User Financial Report structure)
     */
    private function getUserMonthlyFinancialData(int $userId, string $startMonth, string $endMonth): array
    {
        // Generate list of months
        $months = [];
        $current = Carbon::parse($startMonth);
        $end = Carbon::parse($endMonth);

        while ($current <= $end) {
            $months[] = $current->format('Y-m-01');
            $current->addMonth();
        }

        // Get user transactions grouped by month
        $transactions = UserTransaction::where('user_id', $userId)
            ->where('status', 'done')
            ->whereBetween('transaction_date', [$startMonth, $end->endOfMonth()])
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m-01') as month_date")
            ->selectRaw("SUM(CASE WHEN transaction_type = 'deposit' THEN amount ELSE 0 END) as deposits")
            ->selectRaw("SUM(CASE WHEN transaction_type = 'withdrawal' THEN amount ELSE 0 END) as withdrawals")
            ->groupBy('month_date')
            ->get()
            ->keyBy('month_date');

        // Build monthly data
        $monthlyData = [];
        $previousEquity = 0;

        // Get equity from before start month
        $depositsBeforeStart = UserTransaction::where('user_id', $userId)
            ->where('transaction_type', 'deposit')
            ->where('status', 'done')
            ->where('transaction_date', '<', $startMonth)
            ->sum('amount');

        $withdrawalsBeforeStart = UserTransaction::where('user_id', $userId)
            ->where('transaction_type', 'withdrawal')
            ->where('status', 'done')
            ->where('transaction_date', '<', $startMonth)
            ->sum('amount');

        $previousEquity = $depositsBeforeStart - $withdrawalsBeforeStart;

        foreach ($months as $month) {
            $monthEnd = Carbon::parse($month)->endOfMonth();
            $deposits = $transactions[$month]->deposits ?? 0;
            $withdrawals = $transactions[$month]->withdrawals ?? 0;

            // Calculate cumulative equity
            $equity = $previousEquity + $deposits - $withdrawals;

            // Get equity percentage for this month
            $equityPercentage = $this->getUserEquityPercentage($userId, $monthEnd);

            // Get company profit for this month
            $companyProfit = $this->getCurrentMonthProjectsProfit($monthEnd);

            // Calculate user profit using previous month's equity percentage
            $previousMonthEnd = Carbon::parse($month)->subMonth()->endOfMonth();
            $previousEquityPercentage = $this->getUserEquityPercentage($userId, $previousMonthEnd);
            $equityFraction = $previousEquityPercentage / 100;
            $totalProfit = $equityFraction * $companyProfit;

            // Get asset and operation profit breakdown
            $assetCompanyProfit = $this->calculateCurrentMonthAssetProfit($monthEnd);
            $operationCompanyProfit = $this->calculateCurrentMonthOperationProfit($monthEnd);
            $assetProfit = $equityFraction * $assetCompanyProfit;
            $operationProfit = $equityFraction * $operationCompanyProfit;

            $monthlyData[] = [
                'month' => $month,
                'month_name' => Carbon::parse($month)->format('M Y'),
                'deposits' => (float) $deposits,
                'withdrawals' => (float) $withdrawals,
                'equity' => (float) $equity,
                'equity_percentage' => round($equityPercentage, 2),
                'profit_asset' => round($assetProfit, 2),
                'profit_operation' => round($operationProfit, 2),
                'total_profit' => round($totalProfit, 2)
            ];

            $previousEquity = $equity;
        }

        return $monthlyData;
    }

    /**
     * Calculate total deposits for user
     */
    private function calculateTotalDeposits(int $userId): float
    {
        return UserTransaction::where('user_id', $userId)
            ->where('transaction_type', 'deposit')
            ->where('status', 'done')
            ->sum('amount');
    }

    /**
     * Calculate total withdrawals for user
     */
    private function calculateTotalWithdrawals(int $userId): float
    {
        return UserTransaction::where('user_id', $userId)
            ->where('transaction_type', 'withdrawal')
            ->where('status', 'done')
            ->sum('amount');
    }
}
