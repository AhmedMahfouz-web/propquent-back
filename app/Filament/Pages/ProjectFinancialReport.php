<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Project;
use App\Models\ProjectTransaction;
use Illuminate\Support\Facades\DB;

use Livewire\Attributes\Url;
use Livewire\WithPagination;

class ProjectFinancialReport extends Page
{
    use WithPagination;

    #[Url]
    public $search = '';

    public $perPage = 25;

    public $allMonths = [];

    public function mount(): void
    {
        $this->allMonths = ProjectTransaction::select(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01") as month_date'))
            ->distinct()
            ->orderBy('month_date', 'asc')
            ->pluck('month_date')
            ->toArray();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function getProjectsProperty()
    {
        return Project::query()
            ->when($this->search, fn ($query) => $query->where('title', 'like', '%' . $this->search . '%'))
            ->paginate($this->perPage);
    }

    public function getFinancialSummaryProperty()
    {
        $projects = $this->projects;
        $projectKeys = $projects->pluck('key')->toArray();

        $transactions = ProjectTransaction::select(
            'project_key',
            DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01") as month_date'),
            DB::raw('SUM(CASE WHEN financial_type = \'revenue\' THEN amount ELSE 0 END) as total_revenue'),
            DB::raw('SUM(CASE WHEN financial_type = \'expense\' THEN amount ELSE 0 END) as total_expense')
        )
            ->whereIn('project_key', $projectKeys)
            ->groupBy('project_key', 'month_date')
            ->get();

        $summary = [];
        $previousMonthEvaluationAsset = [];

        foreach ($projects as $project) {
            $projectKey = $project->key;
            $previousMonthEvaluationAsset[$projectKey] = 0;

            $projectData = [
                'title' => $project->title,
                'status' => $project->status,
                'months' => [],
                'totals' => [
                    'revenue' => 0,
                    'expense' => 0,
                    'profit' => 0,
                    'profit_operation' => 0,
                    'profit_asset' => 0,
                    'evaluation_asset' => 0, // This will hold the latest evaluation asset value
                ]
            ];

            $projectTransactions = $transactions->where('project_key', $project->key);

            $isFirstMonth = true;
            foreach ($this->allMonths as $month) {
                $monthData = $projectTransactions->where('month_date', $month)->first();
                $revenue = $monthData->total_revenue ?? 0;
                $expense = $monthData->total_expense ?? 0;

                $evaluationAsset = $previousMonthEvaluationAsset[$projectKey] + $expense - $revenue;
                $evaluationAsset = $evaluationAsset < 0 ? 0 : $evaluationAsset;

                $profitOperation = $revenue - $expense;
                $profitAsset = 0; // Placeholder
                $totalProfit = $profitOperation + $profitAsset;

                $projectData['months'][$month] = [
                    'revenue' => $revenue,
                    'expense' => $expense,
                    'profit' => $totalProfit,
                    'evaluation_asset' => $evaluationAsset,
                    'profit_asset' => $profitAsset,
                    'profit_operation' => $profitOperation,
                ];

                $projectData['totals']['revenue'] += $revenue;
                $projectData['totals']['expense'] += $expense;
                $projectData['totals']['profit'] += $totalProfit;
                $projectData['totals']['profit_operation'] += $profitOperation;
                $projectData['totals']['profit_asset'] += $profitAsset;

                // The total for evaluation asset is the last calculated value for the most recent month
                if ($isFirstMonth) { // Since months are sorted descending, the first month is the latest
                    $projectData['totals']['evaluation_asset'] = $evaluationAsset;
                    $isFirstMonth = false;
                }

                $previousMonthEvaluationAsset[$projectKey] = $evaluationAsset;
            }
            $summary[$projectKey] = $projectData;
        }

        return $summary;
    }

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static string $view = 'filament.pages.project-financial-report';

    protected static ?string $navigationLabel = 'Project Financial Report';

    protected static ?string $title = 'Project Financial Report';

    protected static ?string $navigationGroup = 'Financial Reports';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return null;
    }
}
