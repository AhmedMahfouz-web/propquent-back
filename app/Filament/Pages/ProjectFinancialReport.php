<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Project;
use App\Models\ProjectTransaction;
use Illuminate\Support\Facades\DB;

class ProjectFinancialReport extends Page
{
    public $projects;
    public $financialSummary = [];
    public $allMonths = [];

    public function mount(): void
    {
        $this->projects = Project::with('transactions')->get();

        $transactions = ProjectTransaction::select(
            'project_key',
            DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01") as month_date'),
            DB::raw('SUM(CASE WHEN financial_type = \'revenue\' THEN amount ELSE 0 END) as total_revenue'),
            DB::raw('SUM(CASE WHEN financial_type = \'expense\' THEN amount ELSE 0 END) as total_expense')
        )
            ->groupBy('project_key', 'month_date')
            ->orderBy('month_date', 'desc')
            ->get();

        $this->allMonths = $transactions->pluck('month_date')->unique()->sortDesc();

        $summary = [];
        foreach ($this->projects as $project) {
            $projectData = [
                'title' => $project->title,
                'status' => $project->status,
                'months' => [],
                'totals' => [
                    'revenue' => 0,
                    'expense' => 0,
                    'profit' => 0,
                ]
            ];

            $projectTransactions = $transactions->where('project_key', $project->key);

            foreach ($this->allMonths as $month) {
                $monthData = $projectTransactions->where('month_date', $month)->first();
                $revenue = $monthData->total_revenue ?? 0;
                $expense = $monthData->total_expense ?? 0;
                $profit = $revenue - $expense;

                $projectData['months'][$month] = [
                    'revenue' => $revenue,
                    'expense' => $expense,
                    'profit' => $profit,
                ];

                $projectData['totals']['revenue'] += $revenue;
                $projectData['totals']['expense'] += $expense;
                $projectData['totals']['profit'] += $profit;
            }
            $summary[$project->key] = $projectData;
        }

        $this->financialSummary = $summary;
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
