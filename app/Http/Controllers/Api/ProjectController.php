<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\ProjectTransaction;
use App\Models\UserTransaction;
use App\Models\MonthlyProjectEvaluation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProjectController extends BaseApiController
{
    protected string $model = Project::class;
    protected ?string $resource = ProjectResource::class;
    protected array $searchableFields = [
        'project_key',
        'title',
        'description',
        'unit',
        'compound'
    ];

    protected array $filterableFields = [
        'status',
        'stage',
        'type',
        'investment_type',
        'developer_id'
    ];

    protected array $sortableFields = [
        'id',
        'project_key',
        'title',
        'area',
        'total_contract_value',
        'reservation_date',
        'contract_date',
        'created_at',
        'updated_at'
    ];

    /**
     * Get all projects with images - Override parent to include media
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = $this->model::query()->with(['media', 'developer', 'compound']);

            // Apply search
            $this->applySearch($query, $request);

            // Apply filters
            $this->applyFilters($query, $request);

            // Apply sorting
            $this->applySorting($query, $request);

            // Get pagination parameters
            $perPage = min(
                $request->get('per_page', $this->perPage),
                $this->maxPerPage
            );

            // Paginate results
            $results = $query->paginate($perPage);

            // Transform using resource if available
            if ($this->resource) {
                $results->getCollection()->transform(function ($item) {
                    return new $this->resource($item);
                });
            }

            // Calculate user's total profit from ALL projects (not just paginated results)
            $user = Auth::user();
            $currentDate = Carbon::now();
            $currentMonth = $currentDate->format('Y-m-01');
            
            // Get all projects and calculate user's total profit
            $allProjects = Project::all();
            $totalUserProfit = 0;
            
            foreach ($allProjects as $project) {
                $financialData = $this->getProjectFinancialData($project, $user->id);
                $totalUserProfit += $financialData['total_profit'];
            }

            return response()->json([
                'success' => true,
                'message' => 'Resources retrieved successfully',
                'data' => $results->items(),
                'asset_value' => round($totalUserProfit, 2),
                'pagination' => [
                    'current_page' => $results->currentPage(),
                    'per_page' => $results->perPage(),
                    'total' => $results->total(),
                    'last_page' => $results->lastPage(),
                    'from' => $results->firstItem(),
                    'to' => $results->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve resources',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific project with images - Override parent to include media
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $resource = $this->model::with(['media', 'developer', 'compound'])->findOrFail($id);

            $data = $this->resource ? new $this->resource($resource) : $resource;
            
            // Add financial data with user's share (user equity % × project values)
            $financialData = $this->getProjectFinancialData($resource, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Resource retrieved successfully',
                'data' => $data,
                'financial_data' => $financialData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
    
    /**
     * Get financial data for a project showing user's share
     * Total profit = sum of each month's (user's equity% * previous month's project profit)
     */
    private function getProjectFinancialData($project, int $userId): array
    {
        // Get user's current equity percentage for investment amount calculation
        $currentDate = Carbon::now();
        $userEquityPercentage = $this->getUserEquityPercentageForHome($userId, $currentDate);
        $equityFraction = $userEquityPercentage / 100;
        
        // Get all monthly evaluation data from database
        $allTimeTotals = \App\Models\MonthlyProjectEvaluation::where('project_key', $project->key)
            ->selectRaw('
                SUM(expense_asset) as total_expense_asset,
                SUM(revenue_asset) as total_revenue_asset,
                SUM(expense_operation) as total_expense_operation,
                SUM(revenue_operation) as total_revenue_operation,
                SUM(profit_operation) as total_profit_operation
            ')
            ->first();
        
        // Calculate total investment amount (total expenses)
        $projectInvestmentAmount = 0;
        if ($allTimeTotals) {
            $projectInvestmentAmount = (float) $allTimeTotals->total_expense_asset + (float) $allTimeTotals->total_expense_operation;
        }
        
        // Get all monthly data ordered chronologically
        $allMonthlyData = \App\Models\MonthlyProjectEvaluation::where('project_key', $project->key)
            ->orderBy('month_date', 'asc')
            ->get();
        
        // Calculate project profit for each month
        $projectProfitByMonth = [];
        $previousAssetEvaluation = 0;
        
        foreach ($allMonthlyData as $monthData) {
            $month = $monthData->month_date->format('Y-m-01');
            
            // Calculate asset profit for this month
            $currentAssetEvaluation = (float) $monthData->asset_evaluation;
            $revenueAsset = (float) $monthData->revenue_asset;
            $expenseAsset = (float) $monthData->expense_asset;
            $monthlyProfitAsset = $currentAssetEvaluation - $previousAssetEvaluation + $revenueAsset - $expenseAsset;
            
            // Operation profit for this month
            $monthlyProfitOperation = (float) $monthData->profit_operation;
            
            // Total profit for this month
            $projectProfitByMonth[$month] = [
                'asset' => $monthlyProfitAsset,
                'operation' => $monthlyProfitOperation,
                'total' => $monthlyProfitAsset + $monthlyProfitOperation
            ];
            
            $previousAssetEvaluation = $currentAssetEvaluation;
        }
        
        // Calculate user's total profit: sum of (user's equity% of previous month * project profit of current month)
        $userTotalProfit = 0;
        $userAssetProfit = 0;
        $userOperationProfit = 0;
        $previousMonth = null;
        
        foreach ($allMonthlyData as $monthData) {
            $month = $monthData->month_date->format('Y-m-01');
            
            // Get user's equity percentage for the PREVIOUS month
            $userEquityForPreviousMonth = 0;
            if ($previousMonth) {
                $previousMonthDate = Carbon::parse($previousMonth)->endOfMonth();
                $userEquityForPreviousMonth = $this->getUserEquityPercentageForHome($userId, $previousMonthDate);
            }
            // First month: equity% = 0 (no previous month)
            
            $equityFractionForMonth = $userEquityForPreviousMonth / 100;
            
            // Calculate user's profit for this month
            $projectProfit = $projectProfitByMonth[$month];
            $userTotalProfit += $equityFractionForMonth * $projectProfit['total'];
            $userAssetProfit += $equityFractionForMonth * $projectProfit['asset'];
            $userOperationProfit += $equityFractionForMonth * $projectProfit['operation'];
            
            $previousMonth = $month;
        }
        
        // Apply current user's equity percentage to investment amount
        return [
            'investment_amount' => round($equityFraction * $projectInvestmentAmount, 2),
            'total_profit' => round($userTotalProfit, 2),
            'operation_profit' => round($userOperationProfit, 2),
            'asset_profit' => round($userAssetProfit, 2),
            'currency' => 'USD'
        ];
    }
    
    /**
     * Get user's equity percentage (same calculation as HomeController)
     */
    private function getUserEquityPercentageForHome(int $userId, Carbon $endDate): float
    {
        // Calculate user's cumulative equity up to the given date
        $userEquity = $this->calculateUserCumulativeEquity($userId, $endDate);

        // Calculate company total equity (cash + asset evaluation)
        $companyTotalEquity = $this->calculateCompanyTotalEquity($endDate);

        if ($companyTotalEquity == 0) {
            return 0;
        }

        // Return as percentage (multiply by 100)
        return ($userEquity / $companyTotalEquity) * 100;
    }
    
    /**
     * Calculate user's cumulative equity (deposits - withdrawals)
     */
    private function calculateUserCumulativeEquity(int $userId, Carbon $endDate): float
    {
        $deposits = UserTransaction::where('user_id', $userId)
            ->where('transaction_type', UserTransaction::TYPE_DEPOSIT)
            ->where('status', UserTransaction::STATUS_DONE)
            ->where('transaction_date', '<=', $endDate)
            ->sum('amount');

        $withdrawals = UserTransaction::where('user_id', $userId)
            ->where('transaction_type', UserTransaction::TYPE_WITHDRAWAL)
            ->where('status', UserTransaction::STATUS_DONE)
            ->where('transaction_date', '<=', $endDate)
            ->sum('amount');

        return $deposits - $withdrawals;
    }
    
    /**
     * Calculate company total equity (cash + asset evaluation)
     */
    private function calculateCompanyTotalEquity(Carbon $endDate): float
    {
        $month = $endDate->format('Y-m-01');

        // Try to get cached cash balance first
        $cachedCash = \App\Models\MonthlyCashBalance::where('month_date', $month)->first();

        if ($cachedCash) {
            $cash = (float) $cachedCash->cash_balance;
        } else {
            // Fallback: Calculate manually
            $allDeposits = UserTransaction::where('transaction_type', UserTransaction::TYPE_DEPOSIT)
                ->where('status', UserTransaction::STATUS_DONE)
                ->where('transaction_date', '<=', $endDate)
                ->sum('amount');

            $allWithdrawals = UserTransaction::where('transaction_type', UserTransaction::TYPE_WITHDRAWAL)
                ->where('status', UserTransaction::STATUS_DONE)
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
     * Store method disabled - Projects can only be managed through Filament admin
     */
    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Project creation is not allowed through API. Please use the admin panel.',
            'error' => 'Method not allowed'
        ], 405);
    }

    /**
     * Update method disabled - Projects can only be managed through Filament admin
     */
    public function update(Request $request, $id): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Project updates are not allowed through API. Please use the admin panel.',
            'error' => 'Method not allowed'
        ], 405);
    }

    /**
     * Delete method disabled - Projects can only be managed through Filament admin
     */
    public function destroy($id): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Project deletion is not allowed through API. Please use the admin panel.',
            'error' => 'Method not allowed'
        ], 405);
    }

    /**
     * Validate store request - Not used since store is disabled
     * Projects can only be created through Filament admin
     */
    protected function validateStoreRequest(Request $request): array
    {
        // Since store method is disabled, this validation is not used
        // But we need to implement it to satisfy the abstract method requirement
        throw new \Exception('Project creation is not allowed through API. Please use the admin panel.');
    }

    /**
     * Validate update request - Not used since update is disabled
     * Projects can only be updated through Filament admin
     */
    protected function validateUpdateRequest(Request $request, Model $resource): array
    {
        // Since update method is disabled, this validation is not used
        // But we need to implement it to satisfy the abstract method requirement
        throw new \Exception('Project updates are not allowed through API. Please use the admin panel.');
    }

    /**
     * Get projects list with user's financial data
     */
    public function projectsList(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $currentDate = Carbon::now();
            $currentMonth = $currentDate->format('Y-m-01');

            // Get user's current equity percentage for display
            $userEquityPercentage = $this->getUserEquityPercentageForHome($user->id, $currentDate);

            // Get all projects with their financial data and images
            $projects = Project::with(['developer', 'media'])
                ->get()
                ->map(function ($project) use ($user, $currentMonth) {
                    return $this->enrichProjectWithFinancialData($project, $user->id, $currentMonth);
                });

            // Calculate total user profit from all projects (sum of user_total_profit from each project)
            $totalUserProfit = $projects->sum(function ($project) {
                return $project['financial_data']['user_total_profit'] ?? 0;
            });

            // Calculate summary financial data
            $totalNonExitedProjectsAmount = $this->calculateTotalNonExitedProjectsAmount();

            return response()->json([
                'success' => true,
                'message' => 'Projects list retrieved successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'full_name' => $user->full_name,
                        'custom_id' => $user->custom_id,
                        'equity_percentage' => round($userEquityPercentage, 2)
                    ],
                    'financial_summary' => [
                        'asset_value' => round($totalUserProfit, 2),
                        'total_non_exited_projects_amount' => $totalNonExitedProjectsAmount,
                        'currency' => 'USD'
                    ],
                    'projects' => $projects,
                    'projects_count' => $projects->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve projects list',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enrich project with user's financial data
     * Uses same logic: sum of (user's equity% of previous month × project profit of current month)
     */
    private function enrichProjectWithFinancialData(Project $project, int $userId, string $currentMonth): array
    {
        // Get current month's asset evaluation from database (matches Project Financial Report)
        $assetEvaluation = MonthlyProjectEvaluation::getLatestAssetEvaluation($project->key);

        // Get user's current equity percentage for investment amount calculation
        $currentDate = Carbon::now();
        $userEquityPercentage = $this->getUserEquityPercentageForHome($userId, $currentDate);
        $equityFraction = $userEquityPercentage / 100;

        // Get all monthly evaluation data
        $allTimeTotals = MonthlyProjectEvaluation::where('project_key', $project->key)
            ->selectRaw('
                SUM(expense_asset) as total_expense_asset,
                SUM(revenue_asset) as total_revenue_asset,
                SUM(expense_operation) as total_expense_operation,
                SUM(revenue_operation) as total_revenue_operation
            ')
            ->first();
        
        // Calculate total investment amount (total expenses)
        $projectInvestmentAmount = 0;
        if ($allTimeTotals) {
            $projectInvestmentAmount = (float) $allTimeTotals->total_expense_asset + (float) $allTimeTotals->total_expense_operation;
        }

        // Get all monthly data ordered chronologically
        $allMonthlyData = MonthlyProjectEvaluation::where('project_key', $project->key)
            ->orderBy('month_date', 'asc')
            ->get();
        
        // Calculate project profit for each month
        $projectProfitByMonth = [];
        $previousAssetEvaluation = 0;
        
        foreach ($allMonthlyData as $monthData) {
            $month = $monthData->month_date->format('Y-m-01');
            
            // Calculate asset profit for this month
            $currentAssetEvaluation = (float) $monthData->asset_evaluation;
            $revenueAsset = (float) $monthData->revenue_asset;
            $expenseAsset = (float) $monthData->expense_asset;
            $monthlyProfitAsset = $currentAssetEvaluation - $previousAssetEvaluation + $revenueAsset - $expenseAsset;
            
            // Operation profit for this month
            $monthlyProfitOperation = (float) $monthData->profit_operation;
            
            // Total profit for this month
            $projectProfitByMonth[$month] = [
                'asset' => $monthlyProfitAsset,
                'operation' => $monthlyProfitOperation,
                'total' => $monthlyProfitAsset + $monthlyProfitOperation
            ];
            
            $previousAssetEvaluation = $currentAssetEvaluation;
        }
        
        // Calculate user's total profit: sum of (user's equity% of previous month * project profit of current month)
        $userTotalProfit = 0;
        $userAssetProfit = 0;
        $userOperationProfit = 0;
        $previousMonth = null;
        
        foreach ($allMonthlyData as $monthData) {
            $month = $monthData->month_date->format('Y-m-01');
            
            // Get user's equity percentage for the PREVIOUS month
            $userEquityForPreviousMonth = 0;
            if ($previousMonth) {
                $previousMonthDate = Carbon::parse($previousMonth)->endOfMonth();
                $userEquityForPreviousMonth = $this->getUserEquityPercentageForHome($userId, $previousMonthDate);
            }
            // First month: equity% = 0 (no previous month)
            
            $equityFractionForMonth = $userEquityForPreviousMonth / 100;
            
            // Calculate user's profit for this month
            $projectProfit = $projectProfitByMonth[$month];
            $userTotalProfit += $equityFractionForMonth * $projectProfit['total'];
            $userAssetProfit += $equityFractionForMonth * $projectProfit['asset'];
            $userOperationProfit += $equityFractionForMonth * $projectProfit['operation'];
            
            $previousMonth = $month;
        }

        // Calculate user's invested amount using current equity
        $userInvestedAmount = $equityFraction * $projectInvestmentAmount;

        // Get project images with URLs
        $images = $project->getMedia('images')->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'url' => $media->getUrl(),
                'thumbnail_url' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
                'preview_url' => $media->hasGeneratedConversion('preview') ? $media->getUrl('preview') : $media->getUrl(),
                'created_at' => $media->created_at,
            ];
        });

        return [
            'id' => $project->id,
            'key' => $project->key,
            'title' => $project->title,
            'description' => $project->description,
            'unit' => $project->unit,
            'area' => $project->area,
            'garden_area' => $project->garden_area,
            'compound' => $project->compound,
            'status' => $project->status,
            'stage' => $project->stage,
            'type' => $project->type,
            'investment_type' => $project->investment_type,
            'reservation_date' => $project->reservation_date,
            'contract_date' => $project->contract_date,
            'total_contract_value' => $project->total_contract_value,
            'years' => $project->years,
            'notes' => $project->notes,
            'developer' => [
                'id' => $project->developer->id ?? null,
                'name' => $project->developer->name ?? null,
            ],
            'images' => $images,
            'financial_data' => [
                'asset_evaluation' => (float) $assetEvaluation,
                'user_invested_amount' => round($userInvestedAmount, 2),
                'user_total_profit' => round($userTotalProfit, 2),
                'user_asset_profit' => round($userAssetProfit, 2),
                'user_operation_profit' => round($userOperationProfit, 2),
                'currency' => 'USD'
            ],
            'created_at' => $project->created_at,
            'updated_at' => $project->updated_at
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
     * Calculate total asset value (asset revenue - asset expenses)
     */
    private function calculateTotalAssetValue(): float
    {
        $assetRevenue = ProjectTransaction::where('financial_type', 'revenue')
            ->where('serving', 'asset')
            ->where('status', 'completed')
            ->sum('amount');

        $assetExpenses = ProjectTransaction::where('financial_type', 'expense')
            ->where('serving', 'asset')
            ->where('status', 'completed')
            ->sum('amount');

        return $assetRevenue - $assetExpenses;
    }

    /**
     * Calculate total amount of non-exited projects
     */
    private function calculateTotalNonExitedProjectsAmount(): float
    {
        // Get all non-exited projects
        $nonExitedProjects = Project::where('status', '!=', Project::STATUS_EXITED)->get();

        $totalAmount = 0;
        foreach ($nonExitedProjects as $project) {
            // Sum up the net revenue for each non-exited project
            $projectRevenue = ProjectTransaction::where('project_key', $project->key)
                ->where('financial_type', 'revenue')
                ->where('status', 'completed')
                ->sum('amount');

            $projectExpenses = ProjectTransaction::where('project_key', $project->key)
                ->where('financial_type', 'expense')
                ->where('status', 'completed')
                ->sum('amount');

            $totalAmount += ($projectRevenue - $projectExpenses);
        }

        return $totalAmount;
    }
}
