<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserTransaction;
use App\Models\ProjectTransaction;
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
            ->where('status', 'completed')
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
     * Calculate total profit for user (previous month equity * current month projects profit)
     */
    private function calculateTotalProfit(int $userId, Carbon $endDate): float
    {
        $previousMonthEquity = $this->getUserEquityPercentage($userId, $endDate->copy()->subMonth());
        $currentMonthProjectsProfit = $this->getCurrentMonthProjectsProfit($endDate);
        
        return $previousMonthEquity * $currentMonthProjectsProfit;
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
            ->where('status', 'completed')
            ->sum('amount');

        $totalWithdrawals = UserTransaction::where('user_id', $userId)
            ->where('transaction_type', 'withdrawal')
            ->where('status', 'completed')
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

            $monthData = [
                'month' => $current->format('Y-m'),
                'month_name' => $current->format('M Y'),
                'capital_investment' => $this->calculateCapitalInvestment($userId, $monthEnd),
                'profit' => $this->calculateTotalProfit($userId, $monthEnd)
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
     * Calculate user's equity percentage based on their investment
     */
    private function getUserEquityPercentage(int $userId, Carbon $endDate): float
    {
        // Get user's total investment (deposits) up to the given date
        $userInvestment = UserTransaction::where('user_id', $userId)
            ->where('transaction_type', 'deposit')
            ->where('status', 'completed')
            ->where('transaction_date', '<=', $endDate)
            ->sum('amount');

        // Get total investment from all users up to the given date
        $totalInvestment = UserTransaction::where('transaction_type', 'deposit')
            ->where('status', 'completed')
            ->where('transaction_date', '<=', $endDate)
            ->sum('amount');

        if ($totalInvestment == 0) {
            return 0;
        }

        return $userInvestment / $totalInvestment;
    }

    /**
     * Calculate current month's total projects profit (revenue - expenses)
     */
    private function getCurrentMonthProjectsProfit(Carbon $endDate): float
    {
        $monthStart = $endDate->copy()->startOfMonth();

        // Get revenue from project transactions for current month
        $revenue = ProjectTransaction::where('financial_type', 'revenue')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$monthStart, $endDate])
            ->sum('amount');

        // Get expenses from project transactions for current month
        $expenses = ProjectTransaction::where('financial_type', 'expense')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$monthStart, $endDate])
            ->sum('amount');

        return $revenue - $expenses;
    }

    /**
     * Calculate asset profit (previous month equity * current month asset projects profit)
     */
    private function calculateAssetProfit(int $userId, Carbon $endDate): float
    {
        $previousMonthEquity = $this->getUserEquityPercentage($userId, $endDate->copy()->subMonth());
        $currentMonthAssetProfit = $this->getCurrentMonthAssetProjectsProfit($endDate);
        
        return $previousMonthEquity * $currentMonthAssetProfit;
    }

    /**
     * Calculate operation profit (previous month equity * current month operation projects profit)
     */
    private function calculateOperationProfit(int $userId, Carbon $endDate): float
    {
        $previousMonthEquity = $this->getUserEquityPercentage($userId, $endDate->copy()->subMonth());
        $currentMonthOperationProfit = $this->getCurrentMonthOperationProjectsProfit($endDate);
        
        return $previousMonthEquity * $currentMonthOperationProfit;
    }

    /**
     * Calculate current month's asset projects profit
     */
    private function getCurrentMonthAssetProjectsProfit(Carbon $endDate): float
    {
        $monthStart = $endDate->copy()->startOfMonth();

        // Get revenue from asset project transactions for current month
        $revenue = ProjectTransaction::where('financial_type', 'revenue')
            ->where('serving', 'asset')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$monthStart, $endDate])
            ->sum('amount');

        // Get expenses from asset project transactions for current month
        $expenses = ProjectTransaction::where('financial_type', 'expense')
            ->where('serving', 'asset')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$monthStart, $endDate])
            ->sum('amount');

        return $revenue - $expenses;
    }

    /**
     * Calculate current month's operation projects profit
     */
    private function getCurrentMonthOperationProjectsProfit(Carbon $endDate): float
    {
        $monthStart = $endDate->copy()->startOfMonth();

        // Get revenue from operation project transactions for current month
        $revenue = ProjectTransaction::where('financial_type', 'revenue')
            ->where('serving', 'operation')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$monthStart, $endDate])
            ->sum('amount');

        // Get expenses from operation project transactions for current month
        $expenses = ProjectTransaction::where('financial_type', 'expense')
            ->where('serving', 'operation')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$monthStart, $endDate])
            ->sum('amount');

        return $revenue - $expenses;
    }

    /**
     * Get user profile with financial data and transactions
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $currentDate = Carbon::now();

            // Get user's equity percentage
            $userEquity = $this->getUserEquityPercentage($user->id, $currentDate);

            // Calculate financial metrics
            $deposits = $this->calculateTotalDeposits($user->id);
            $withdrawals = $this->calculateTotalWithdrawals($user->id);
            $equity = $deposits - $withdrawals;
            $totalProfit = $this->calculateTotalProfit($user->id, $currentDate);
            $assetProfit = $this->calculateAssetProfit($user->id, $currentDate);
            $operationProfit = $this->calculateOperationProfit($user->id, $currentDate);

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
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at
                    ],
                    'financial_metrics' => [
                        'deposits' => $deposits,
                        'withdrawals' => $withdrawals,
                        'equity' => $equity,
                        'equity_percentage' => round($userEquity * 100, 2),
                        'total_profit' => $totalProfit,
                        'profit_asset' => $assetProfit,
                        'profit_operation' => $operationProfit,
                        'currency' => 'USD'
                    ],
                    'recent_transactions' => $recentTransactions
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
     * Calculate total deposits for user
     */
    private function calculateTotalDeposits(int $userId): float
    {
        return UserTransaction::where('user_id', $userId)
            ->where('transaction_type', 'deposit')
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Calculate total withdrawals for user
     */
    private function calculateTotalWithdrawals(int $userId): float
    {
        return UserTransaction::where('user_id', $userId)
            ->where('transaction_type', 'withdrawal')
            ->where('status', 'completed')
            ->sum('amount');
    }
}
