<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\ProjectTransaction;
use App\Models\UserTransaction;
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

            // Get user's equity percentage
            $userEquity = $this->getUserEquityPercentage($user->id, $currentDate);

            // Get all projects with their financial data
            $projects = Project::with(['developer'])
                ->get()
                ->map(function ($project) use ($userEquity) {
                    return $this->enrichProjectWithFinancialData($project, $userEquity);
                });

            // Calculate summary financial data
            $totalAssetValue = $this->calculateTotalAssetValue();
            $totalNonExitedProjectsAmount = $this->calculateTotalNonExitedProjectsAmount();

            return response()->json([
                'success' => true,
                'message' => 'Projects list retrieved successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'full_name' => $user->full_name,
                        'custom_id' => $user->custom_id,
                        'equity_percentage' => round($userEquity * 100, 2)
                    ],
                    'financial_summary' => [
                        'total_asset_value' => $totalAssetValue,
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
     */
    private function enrichProjectWithFinancialData(Project $project, float $userEquity): array
    {
        // Calculate project's total revenue and expenses
        $projectRevenue = ProjectTransaction::where('project_key', $project->key)
            ->where('financial_type', 'revenue')
            ->where('status', 'completed')
            ->sum('amount');

        $projectExpenses = ProjectTransaction::where('project_key', $project->key)
            ->where('financial_type', 'expense')
            ->where('status', 'completed')
            ->sum('amount');

        $projectNetRevenue = $projectRevenue - $projectExpenses;
        $projectTotalProfit = $projectNetRevenue; // Assuming profit = net revenue for now

        // Calculate user's invested amount and profit for this project
        $userInvestedAmount = $userEquity * $projectNetRevenue;
        $userProfitFromProject = $userEquity * $projectTotalProfit;

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
            'financial_data' => [
                'project_revenue' => $projectRevenue,
                'project_expenses' => $projectExpenses,
                'project_net_revenue' => $projectNetRevenue,
                'project_total_profit' => $projectTotalProfit,
                'user_invested_amount' => $userInvestedAmount,
                'user_profit_from_project' => $userProfitFromProject,
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
