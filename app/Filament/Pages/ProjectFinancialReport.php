<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Project;
use App\Models\ProjectTransaction;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Filament\Notifications\Notification;

use Livewire\Attributes\Url;
use Livewire\WithPagination;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ProjectFinancialReport extends Page implements HasForms
{
    use WithPagination, InteractsWithForms;

    public $perPage = 10;

    public bool $readyToLoad = false;

    #[Url]
    public $search = '';

    #[Url]
    public $startMonth = '';

    #[Url]
    public $endMonth = '';

    #[Url]
    public $sortDirection = 'desc';

    #[Url]
    public $status = '';

    #[Url]
    public $stage = '';

    #[Url]
    public $type = '';

    #[Url]
    public $investment_type = '';

    #[Url]
    public $selectedMetrics = [];

    public $refreshCounter = 0;


    protected $listeners = ['evaluation-updated' => 'refreshReportData'];

    public function mount(): void
    {
        $availableMonths = $this->getAvailableMonthsProperty();
        $this->startMonth = !empty($this->startMonth) ? $this->startMonth : ($availableMonths[0] ?? '');
        $this->endMonth = !empty($this->endMonth) ? $this->endMonth : (end($availableMonths) ?: '');

        // Default to all metrics if none selected
        if (empty($this->selectedMetrics)) {
            $this->selectedMetrics = array_keys($this->getAvailableMetrics());
        }

        $this->form->fill([
            'search' => $this->search,
            'startMonth' => $this->startMonth,
            'endMonth' => $this->endMonth,
            'status' => $this->status,
            'stage' => $this->stage,
            'type' => $this->type,
            'investment_type' => $this->investment_type,
            'selectedMetrics' => $this->selectedMetrics,
            'perPage' => $this->perPage,
        ]);
    }

    public function form(Form $form): Form
    {
        $monthOptions = $this->getAvailableMonthsProperty();
        $metricOptions = $this->getAvailableMetrics();

        return $form
            ->schema([
                Section::make('Filters')
                    ->columns(4)
                    ->schema([
                        TextInput::make('search')->label('Search Projects')->live(onBlur: true),
                        Select::make('startMonth')->label('Start Month')->options($monthOptions)->live(),
                        Select::make('endMonth')->label('End Month')->options($monthOptions)->live(),
                        Select::make('perPage')->label('Items Per Page')->options([10 => 10, 25 => 25, 50 => 50, 'all' => 'All'])->live(),
                        Select::make('selectedMetrics')
                            ->label('Show Metrics')
                            ->options($metricOptions)
                            ->multiple()
                            ->columnSpanFull()
                            ->live(),
                        Select::make('status')->label('Status')->options(Project::getAvailableStatuses())->live(),
                        Select::make('stage')->label('Stage')->options(Project::getAvailableStages())->live(),
                        Select::make('type')->label('Type')->options(Project::getAvailablePropertyTypes())->live(),
                        Select::make('investment_type')->label('Investment Type')->options(Project::getAvailableInvestmentTypes())->live(),
                    ]),
            ]);
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function updated($property): void
    {
        if (in_array(str_replace('data.', '', $property), ['search', 'startMonth', 'endMonth', 'status', 'stage', 'type', 'investment_type', 'selectedMetrics', 'perPage', 'sortDirection'])) {
            $this->resetPage();
        }
    }

    public function refreshReportData(): void
    {
        unset($this->computedPropertyCache['reportData']);
        Notification::make()->title('Report Updated')->success()->send();
    }

    public function getAvailableMonthsProperty(): array
    {
        $months = ProjectTransaction::select(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01") as month_date'))
            ->distinct()
            ->orderBy('month_date', 'asc')
            ->pluck('month_date')
            ->toArray();
        return array_combine($months, array_map(fn($m) => date('M Y', strtotime($m)), $months));
    }

    #[Computed]
    public function reportData(): array
    {
        if (!$this->readyToLoad) {
            return [
                'projects' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage),
                'projectsData' => [],
                'financialSummary' => [],
                'allMonths' => [],
            ];
        }

        $projectsQuery = Project::query()
            ->when($this->search, fn($q, $s) => $q->where('title', 'like', "%$s%")->orWhere('key', 'like', "%$s%"))
            ->when($this->status, fn($q, $s) => $q->where('status', $s))
            ->when($this->stage, fn($q, $s) => $q->where('stage', $s))
            ->when($this->type, fn($q, $s) => $q->where('type', $s))
            ->when($this->investment_type, fn($q, $s) => $q->where('investment_type', $s));

        $allMonths = $this->getMonthsInRange();
        $financialSummary = $this->calculateFinancialSummary((clone $projectsQuery), $allMonths);

        $projects = (clone $projectsQuery)
            ->with(['transactions', 'statusChanges', 'evaluations'])
            ->orderBy('created_at', $this->sortDirection)
            ->paginate($this->perPage);

        $projectsData = [];
        foreach ($projects as $project) {
            $projectsData[$project->key] = $this->getProjectFinancialData($project, $allMonths);
        }

        return [
            'projects' => $projects,
            'projectsData' => $projectsData,
            'financialSummary' => $financialSummary,
            'allMonths' => $allMonths,
        ];
    }

    private function getMonthsInRange(): array
    {
        $start = new \DateTime($this->startMonth);
        $end = new \DateTime($this->endMonth);
        $interval = new \DateInterval('P1M');
        $period = new \DatePeriod($start, $interval, $end->modify('+1 month'));
        $months = [];
        foreach ($period as $dt) {
            $months[] = $dt->format('Y-m-01');
        }
        return $months;
    }

    private function getProjectFinancialData(Project $project, array $allMonths): array
    {
        $data = ['key' => $project->key, 'title' => $project->title, 'status' => $project->status, 'months' => [], 'totals' => array_fill_keys(['revenue_operation', 'revenue_asset', 'expense_operation', 'expense_asset', 'profit_operation', 'profit_asset', 'total_profit'], 0)];
        foreach ($allMonths as $month) {
            $data['months'][$month] = array_fill_keys(array_keys($data['totals']), 0);
        }
        foreach ($project->transactions as $transaction) {
            $month = date('Y-m-01', strtotime($transaction->transaction_date));
            if (isset($data['months'][$month])) {
                $key = $transaction->type . '_' . $transaction->category;
                if (!isset($data['months'][$month][$key])) {
                    $data['months'][$month][$key] = 0;
                }
                $data['months'][$month][$key] += $transaction->amount;
            }
        }
        foreach ($data['months'] as &$monthData) {
            $monthData['profit_operation'] = $monthData['revenue_operation'] - $monthData['expense_operation'];
            $monthData['profit_asset'] = $monthData['revenue_asset'] - $monthData['expense_asset'];
            $monthData['total_profit'] = $monthData['profit_operation'] + $monthData['profit_asset'];
            foreach ($data['totals'] as $key => &$total) {
                $total += $monthData[$key];
            }
        }
        return $data;
    }

    private function calculateFinancialSummary($projectsQuery, array $allMonths): array
    {
        $summary = ['totals' => array_fill_keys(['revenue_operation', 'revenue_asset', 'expense_operation', 'expense_asset', 'profit_operation', 'profit_asset', 'total_profit', 'evaluation_asset'], 0), 'months' => []];
        foreach ($allMonths as $month) {
            $summary['months'][$month] = $summary['totals'];
        }
        $projectKeys = (clone $projectsQuery)->pluck('key');
        $transactions = ProjectTransaction::whereIn('project_key', $projectKeys)->get();
        foreach ($transactions as $transaction) {
            $month = date('Y-m-01', strtotime($transaction->transaction_date));
            if (isset($summary['months'][$month])) {
                $key = $transaction->type . '_' . $transaction->category;
                if (!isset($summary['months'][$month][$key])) {
                    $summary['months'][$month][$key] = 0;
                }
                $summary['months'][$month][$key] += $transaction->amount;
            }
        }
        foreach ($summary['months'] as &$monthData) {
            $monthData['profit_operation'] = $monthData['revenue_operation'] - $monthData['expense_operation'];
            $monthData['profit_asset'] = $monthData['revenue_asset'] - $monthData['expense_asset'];
            $monthData['total_profit'] = $monthData['profit_operation'] + $monthData['profit_asset'];
            foreach ($summary['totals'] as $key => &$total) {
                $total += $monthData[$key];
            }
        }
        return $summary;
    }

    public function sortBy($field): void
    {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->resetPage();
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

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exports([
                    ExcelExport::make()
                        ->fromTable()
                        ->withFilename(fn () => 'project-financial-report-' . date('Y-m-d'))
                        ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                ])
        ];
    }

    private function getAvailableMetrics(): array
    {
        return [
            'evaluation_asset' => 'Evaluation Asset',
            'revenue_operation' => 'Revenue Operation',
            'revenue_asset' => 'Revenue Asset',
            'expense_operation' => 'Expense Operation',
            'expense_asset' => 'Expense Asset',
            'profit_operation' => 'Profit Operation',
            'profit_asset' => 'Profit Asset',
            'total_profit' => 'Total Profit',
        ];
    }
}
