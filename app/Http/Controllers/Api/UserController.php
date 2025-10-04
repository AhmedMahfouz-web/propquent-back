<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserTransaction;
use App\Models\ProjectTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserController extends BaseApiController
{
    protected string $model = User::class;
    protected ?string $resource = UserResource::class;

    protected array $searchableFields = [
        'full_name',
        'email',
        'custom_id',
        'phone_number'
    ];

    protected array $filterableFields = [
        'is_active',
        'email_verified',
        'country',
        'theme_color'
    ];

    protected array $sortableFields = [
        'id',
        'full_name',
        'email',
        'custom_id',
        'created_at',
        'updated_at',
        'last_login_at'
    ];


    /**
     * Store method disabled - Users can only be created through registration
     */
    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'User creation is not allowed through this endpoint. Please use the registration endpoint.',
            'error' => 'Method not allowed'
        ], 405);
    }

    /**
     * Update method - User can only update their own profile
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            /** @var User $authUser */
            $authUser = Auth::user();

            // Users can only update their own profile
            if ($authUser->id != $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only update your own profile.',
                    'error' => 'Unauthorized'
                ], 403);
            }

            $data = $request->validate([
                'full_name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
                'password' => 'sometimes|string|min:8|confirmed',
                'phone_number' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:100',
                'profile_picture_url' => 'nullable|url',
                'theme_color' => 'nullable|string|max:50',
                'custom_theme_color' => 'nullable|string|max:7',
            ]);

            // Hash password if provided
            if (isset($data['password'])) {
                $data['password_hash'] = Hash::make($data['password']);
                unset($data['password']);
            }

            $authUser->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => new UserResource($authUser->fresh())
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile with financial data and transactions
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
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
                        'phone_number' => $user->phone_number,
                        'country' => $user->country,
                        'profile_picture_url' => $user->profile_picture_url,
                        'theme_color' => $user->theme_color,
                        'custom_theme_color' => $user->custom_theme_color,
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
     * Validate store request - Not used since store is disabled
     */
    protected function validateStoreRequest(Request $request): array
    {
        throw new \Exception('User creation is not allowed through this endpoint. Please use the registration endpoint.');
    }

    /**
     * Validate update request - Not used since we override update method
     */
    protected function validateUpdateRequest(Request $request, Model $resource): array
    {
        // Not used - validation is done in update method
        return [];
    }

    // Financial calculation methods
    private function getUserEquityPercentage(int $userId, Carbon $endDate): float
    {
        $userInvestment = UserTransaction::where('user_id', $userId)
            ->where('transaction_type', 'deposit')
            ->where('status', 'completed')
            ->where('transaction_date', '<=', $endDate)
            ->sum('amount');

        $totalInvestment = UserTransaction::where('transaction_type', 'deposit')
            ->where('status', 'completed')
            ->where('transaction_date', '<=', $endDate)
            ->sum('amount');

        return $totalInvestment == 0 ? 0 : $userInvestment / $totalInvestment;
    }

    private function calculateTotalDeposits(int $userId): float
    {
        return UserTransaction::where('user_id', $userId)
            ->where('transaction_type', 'deposit')
            ->where('status', 'completed')
            ->sum('amount');
    }

    private function calculateTotalWithdrawals(int $userId): float
    {
        return UserTransaction::where('user_id', $userId)
            ->where('transaction_type', 'withdrawal')
            ->where('status', 'completed')
            ->sum('amount');
    }

    private function calculateTotalProfit(int $userId, Carbon $endDate): float
    {
        $previousMonthEquity = $this->getUserEquityPercentage($userId, $endDate->copy()->subMonth());
        $currentMonthProjectsProfit = $this->getCurrentMonthProjectsProfit($endDate);

        return $previousMonthEquity * $currentMonthProjectsProfit;
    }

    private function calculateAssetProfit(int $userId, Carbon $endDate): float
    {
        $previousMonthEquity = $this->getUserEquityPercentage($userId, $endDate->copy()->subMonth());
        $currentMonthAssetProfit = $this->getCurrentMonthAssetProjectsProfit($endDate);

        return $previousMonthEquity * $currentMonthAssetProfit;
    }

    private function calculateOperationProfit(int $userId, Carbon $endDate): float
    {
        $previousMonthEquity = $this->getUserEquityPercentage($userId, $endDate->copy()->subMonth());
        $currentMonthOperationProfit = $this->getCurrentMonthOperationProjectsProfit($endDate);

        return $previousMonthEquity * $currentMonthOperationProfit;
    }

    private function getCurrentMonthProjectsProfit(Carbon $endDate): float
    {
        $monthStart = $endDate->copy()->startOfMonth();

        $revenue = ProjectTransaction::where('financial_type', 'revenue')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$monthStart, $endDate])
            ->sum('amount');

        $expenses = ProjectTransaction::where('financial_type', 'expense')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$monthStart, $endDate])
            ->sum('amount');

        return $revenue - $expenses;
    }

    private function getCurrentMonthAssetProjectsProfit(Carbon $endDate): float
    {
        $monthStart = $endDate->copy()->startOfMonth();

        $revenue = ProjectTransaction::where('financial_type', 'revenue')
            ->where('serving', 'asset')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$monthStart, $endDate])
            ->sum('amount');

        $expenses = ProjectTransaction::where('financial_type', 'expense')
            ->where('serving', 'asset')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$monthStart, $endDate])
            ->sum('amount');

        return $revenue - $expenses;
    }

    private function getCurrentMonthOperationProjectsProfit(Carbon $endDate): float
    {
        $monthStart = $endDate->copy()->startOfMonth();

        $revenue = ProjectTransaction::where('financial_type', 'revenue')
            ->where('serving', 'operation')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$monthStart, $endDate])
            ->sum('amount');

        $expenses = ProjectTransaction::where('financial_type', 'expense')
            ->where('serving', 'operation')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$monthStart, $endDate])
            ->sum('amount');

        return $revenue - $expenses;
    }

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
}
