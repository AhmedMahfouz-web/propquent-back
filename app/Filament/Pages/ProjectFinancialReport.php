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

    #[Url]
    public $perPage = 25;

    #[Url]
    public $sortDirection = 'desc';

    #[Url]
    public $startMonth = '';

    #[Url]
    public $endMonth = '';

    public $readyToLoad = false;

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function mount(): void
    {
        $availableMonths = ProjectTransaction::select(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01") as month_date'))
            ->distinct()
            ->orderBy('month_date', 'asc')
            ->pluck('month_date')
            ->toArray();

        if (empty($this->startMonth) && !empty($availableMonths)) {
            $this->startMonth = $availableMonths[0];
        }

        if (empty($this->endMonth) && !empty($availableMonths)) {
            $this->endMonth = end($availableMonths);
        }
    }

    public function getAvailableMonthsProperty()
    {
        return ProjectTransaction::select(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01") as month_date'))
            ->distinct()
            ->orderBy('month_date', 'asc')
            ->pluck('month_date')
            ->toArray();
    }

    public function getReportDataProperty()
    {
        if (!$this->readyToLoad) {
            return [
                'projects' => Project::query()->whereRaw('false')->paginate($this->perPage),
                'financialSummary' => [],
                'allMonths' => [],
            ];
        }

        // 1. Get available months and filter them
        $filteredMonths = collect($this->availableMonths)->filter(function ($month) {
            return $month >= $this->startMonth && $month <= $this->endMonth;
        })->sortDesc()->values();

        // 2. Get paginated projects
        $projects = Project::query()
            ->when($this->search, fn ($query) => $query->where('title', 'like', '%' . $this->search . '%'))
            ->orderBy('created_at', $this->sortDirection)
            ->paginate($this->perPage);

        $projectKeys = $projects->pluck('key')->toArray();
        $summary = [];

        if (!empty($projectKeys)) {
            // 3. Get all transactions for the visible projects and date range
            $transactions = ProjectTransaction::query()
                ->whereIn('project_key', $projectKeys)
                ->whereBetween(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01")'), [$this->startMonth, $this->endMonth])
                ->select(
                    'project_key',
                    DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01") as month_date'),
                    DB::raw('SUM(CASE WHEN financial_type = \'revenue\' THEN amount ELSE 0 END) as total_revenue'),
                    DB::raw('SUM(CASE WHEN financial_type = \'expense\' THEN amount ELSE 0 END) as total_expense')
                )
                ->groupBy('project_key', 'month_date')
                ->get()
                ->groupBy('project_key');

            // 4. Process summary
            $chronologicalMonths = $filteredMonths->sort()->values();

            foreach ($projects as $project) {
                $projectKey = $project->key;
                $projectTransactions = $transactions->get($projectKey, collect());
                $projectData = [
                    'title' => $project->title,
                    'status' => $project->status,
                    'months' => [],
                    'totals' => ['revenue' => 0, 'expense' => 0, 'profit' => 0, 'profit_operation' => 0, 'profit_asset' => 0, 'evaluation_asset' => 0]
                ];

                $previousMonthEvaluationAsset = 0;
                foreach ($chronologicalMonths as $month) {
                    $monthData = $projectTransactions->where('month_date', $month)->first();
                    $revenue = $monthData->total_revenue ?? 0;
                    $expense = $monthData->total_expense ?? 0;
                    $evaluationAsset = max(0, $previousMonthEvaluationAsset + $expense - $revenue);
                    $profitOperation = $revenue - $expense;
                    $profitAsset = 0; // Placeholder
                    $totalProfit = $profitOperation + $profitAsset;

                    $projectData['months'][$month] = [
                        'revenue' => $revenue, 'expense' => $expense, 'profit' => $totalProfit,
                        'evaluation_asset' => $evaluationAsset, 'profit_asset' => $profitAsset, 'profit_operation' => $profitOperation
                    ];

                    $projectData['totals']['revenue'] += $revenue;
                    $projectData['totals']['expense'] += $expense;
                    $projectData['totals']['profit'] += $totalProfit;
                    $projectData['totals']['profit_operation'] += $profitOperation;
                    $projectData['totals']['profit_asset'] += $profitAsset;
                    $previousMonthEvaluationAsset = $evaluationAsset;
                }
                $projectData['totals']['evaluation_asset'] = $previousMonthEvaluationAsset;
                $summary[$projectKey] = $projectData;
            }
        }

        return [
            'projects' => $projects,
            'financialSummary' => $summary,
            'allMonths' => $filteredMonths->toArray(),
        ];
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'perPage', 'startMonth', 'endMonth', 'sortDirection'])) {
            $this->resetPage();
        }
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
