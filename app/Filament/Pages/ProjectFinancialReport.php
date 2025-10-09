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


    protected $listeners = ['correction-updated' => 'refreshReportData'];

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
        // Force refresh by resetting the ready state
        $this->readyToLoad = false;
        $this->readyToLoad = true;
        Notification::make()->title('Report Updated')->success()->send();
    }

    public function getAvailableMonthsProperty(): array
    {
        $today = now()->format('Y-m-d');
        
        $months = ProjectTransaction::whereIn('status', ['done', 'pending'])
            ->select(DB::raw('DATE_FORMAT(
                CASE 
                    WHEN status = "done" AND (
                        (actual_date IS NOT NULL AND actual_date <= "' . $today . '") OR 
                        (actual_date IS NULL AND transaction_date <= "' . $today . '") OR
                        (actual_date IS NOT NULL AND actual_date > "' . $today . '") OR
                        (actual_date IS NULL AND transaction_date > "' . $today . '")
                    ) THEN COALESCE(actual_date, transaction_date)
                    WHEN status = "pending" AND transaction_date > "' . $today . '" THEN transaction_date
                    ELSE NULL
                END, "%Y-%m-01") as month_date'))
            ->whereNotNull(DB::raw('CASE 
                WHEN status = "done" AND (
                    (actual_date IS NOT NULL AND actual_date <= "' . $today . '") OR 
                    (actual_date IS NULL AND transaction_date <= "' . $today . '") OR
                    (actual_date IS NOT NULL AND actual_date > "' . $today . '") OR
                    (actual_date IS NULL AND transaction_date > "' . $today . '")
                ) THEN COALESCE(actual_date, transaction_date)
                WHEN status = "pending" AND transaction_date > "' . $today . '" THEN transaction_date
                ELSE NULL
            END'))
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
            ->with(['transactions', 'statusChanges', 'valueCorrections'])
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
        $data = ['key' => $project->key, 'title' => $project->title, 'status' => $project->status, 'months' => [], 'totals' => array_fill_keys(['revenue_operation', 'revenue_asset', 'revenue_total', 'expense_operation', 'expense_asset', 'expense_total', 'profit_operation', 'profit_asset', 'total_profit', 'value_correction', 'evaluation_asset', 'cumulative_cash', 'current_cash', 'projected_cash'], 0)];
        foreach ($allMonths as $month) {
            $data['months'][$month] = array_fill_keys(array_keys($data['totals']), 0);
        }
        $today = now()->startOfDay();
        
        foreach ($project->transactions as $transaction) {
            $dateToUse = null;
            $transactionDate = \Carbon\Carbon::parse($transaction->transaction_date)->startOfDay();
            $actualDate = $transaction->actual_date ? \Carbon\Carbon::parse($transaction->actual_date)->startOfDay() : null;
            
            // Determine how to handle this transaction based on status and dates
            if ($transaction->status === 'done') {
                if ($actualDate && $actualDate->lte($today)) {
                    // Done transaction with actual date in past/present - use actual_date
                    $dateToUse = $transaction->actual_date;
                } elseif (!$actualDate && $transactionDate->lte($today)) {
                    // Done transaction without actual_date but transaction_date in past/present
                    $dateToUse = $transaction->transaction_date;
                } elseif ($actualDate && $actualDate->gt($today)) {
                    // Done transaction with future actual_date - treat as pending, use actual_date for projection
                    $dateToUse = $transaction->actual_date;
                } elseif (!$actualDate && $transactionDate->gt($today)) {
                    // Done transaction with future transaction_date - treat as pending
                    $dateToUse = $transaction->transaction_date;
                } else {
                    continue; // Skip if logic doesn't match
                }
            } elseif ($transaction->status === 'pending') {
                if ($transactionDate->gt($today)) {
                    // Pending transaction with future date - include in projections
                    $dateToUse = $transaction->transaction_date;
                } else {
                    // Pending transaction with past date - ignore (overdue)
                    continue;
                }
            } else {
                // Skip cancelled or other status transactions
                continue;
            }
            
            $month = date('Y-m-01', strtotime($dateToUse));
            
            if (isset($data['months'][$month]) && $transaction->financial_type && $transaction->serving) {
                $key = $transaction->financial_type . '_' . $transaction->serving;
                if (!isset($data['months'][$month][$key])) {
                    $data['months'][$month][$key] = 0;
                }
                $data['months'][$month][$key] += $transaction->amount;
            }
        }
        foreach ($data['months'] as $month => &$monthData) {
            // Calculate derived metrics
            $monthData['profit_operation'] = $monthData['revenue_operation'] - $monthData['expense_operation'];
            $monthData['profit_asset'] = $monthData['revenue_asset'] - $monthData['expense_asset'];
            $monthData['total_profit'] = $monthData['profit_operation'] + $monthData['profit_asset'];
            
            // Calculate new total fields
            $monthData['revenue_total'] = $monthData['revenue_asset'] + $monthData['revenue_operation'];
            $monthData['expense_total'] = $monthData['expense_asset'] + $monthData['expense_operation'];
            
            // Get Value Correction from database
            $monthData['value_correction'] = \App\Models\ValueCorrection::getCorrectionForMonth($project->key, $month);
            
            // Calculate Evaluation Asset = Total Asset Expenses - Total Asset Revenues + Value Correction
            $monthData['evaluation_asset'] = $monthData['expense_asset'] - $monthData['revenue_asset'] + $monthData['value_correction'];
            
            foreach ($data['totals'] as $key => &$total) {
                $total += $monthData[$key];
            }
        }
        
        // Calculate cumulative cashflow for each month
        $this->calculateCumulativeCashflow($data, $allMonths);
        
        return $data;
    }

    private function calculateCumulativeCashflow(array &$data, array $allMonths): void
    {
        $cumulativeCash = 0;
        $today = now()->startOfDay();
        
        // Sort months chronologically
        $sortedMonths = $allMonths;
        sort($sortedMonths);
        
        // First pass: Calculate cumulative cash including all transactions (done + future done + pending future)
        foreach ($sortedMonths as $month) {
            if (isset($data['months'][$month])) {
                // Calculate net cash flow for this month (revenue - expense)
                $monthlyNetCash = $data['months'][$month]['revenue_total'] - $data['months'][$month]['expense_total'];
                
                // Add to cumulative cash
                $cumulativeCash += $monthlyNetCash;
                
                // Store cumulative cash for this month
                $data['months'][$month]['cumulative_cash'] = $cumulativeCash;
                
                // Also store current cash (what we have today including future done transactions)
                $monthDate = \Carbon\Carbon::parse($month)->startOfDay();
                if ($monthDate->lte($today)) {
                    // This month is current or past - include in current cash
                    $data['months'][$month]['current_cash'] = $cumulativeCash;
                } else {
                    // This month is future - show projected cash
                    $data['months'][$month]['current_cash'] = $cumulativeCash;
                    $data['months'][$month]['projected_cash'] = $cumulativeCash;
                }
            }
        }
        
        // Add cumulative_cash to totals structure (this includes all transactions)
        $data['totals']['cumulative_cash'] = $cumulativeCash;
        $data['totals']['current_cash'] = $cumulativeCash; // Show total including future done transactions
    }

    private function calculateFinancialSummary($projectsQuery, array $allMonths): array
    {
        $summary = ['totals' => array_fill_keys(['revenue_operation', 'revenue_asset', 'revenue_total', 'expense_operation', 'expense_asset', 'expense_total', 'profit_operation', 'profit_asset', 'total_profit', 'value_correction', 'evaluation_asset', 'cumulative_cash', 'current_cash', 'projected_cash'], 0), 'months' => []];
        foreach ($allMonths as $month) {
            $summary['months'][$month] = $summary['totals'];
        }
        $projectKeys = (clone $projectsQuery)->pluck('key');
        $transactions = ProjectTransaction::whereIn('project_key', $projectKeys)
            ->whereIn('status', ['done', 'pending'])
            ->get();
        $today = now()->startOfDay();
        
        foreach ($transactions as $transaction) {
            $dateToUse = null;
            $transactionDate = \Carbon\Carbon::parse($transaction->transaction_date)->startOfDay();
            $actualDate = $transaction->actual_date ? \Carbon\Carbon::parse($transaction->actual_date)->startOfDay() : null;
            
            // Determine how to handle this transaction based on status and dates
            if ($transaction->status === 'done') {
                if ($actualDate && $actualDate->lte($today)) {
                    // Done transaction with actual date in past/present - use actual_date
                    $dateToUse = $transaction->actual_date;
                } elseif (!$actualDate && $transactionDate->lte($today)) {
                    // Done transaction without actual_date but transaction_date in past/present
                    $dateToUse = $transaction->transaction_date;
                } elseif ($actualDate && $actualDate->gt($today)) {
                    // Done transaction with future actual_date - treat as pending, use actual_date for projection
                    $dateToUse = $transaction->actual_date;
                } elseif (!$actualDate && $transactionDate->gt($today)) {
                    // Done transaction with future transaction_date - treat as pending
                    $dateToUse = $transaction->transaction_date;
                } else {
                    continue; // Skip if logic doesn't match
                }
            } elseif ($transaction->status === 'pending') {
                if ($transactionDate->gt($today)) {
                    // Pending transaction with future date - include in projections
                    $dateToUse = $transaction->transaction_date;
                } else {
                    // Pending transaction with past date - ignore (overdue)
                    continue;
                }
            } else {
                // Skip cancelled or other status transactions
                continue;
            }
            
            $month = date('Y-m-01', strtotime($dateToUse));
            
            if (isset($summary['months'][$month]) && $transaction->financial_type && $transaction->serving) {
                $key = $transaction->financial_type . '_' . $transaction->serving;
                if (!isset($summary['months'][$month][$key])) {
                    $summary['months'][$month][$key] = 0;
                }
                $summary['months'][$month][$key] += $transaction->amount;
            }
        }
        foreach ($summary['months'] as $month => &$monthData) {
            // Calculate derived metrics
            $monthData['profit_operation'] = $monthData['revenue_operation'] - $monthData['expense_operation'];
            $monthData['profit_asset'] = $monthData['revenue_asset'] - $monthData['expense_asset'];
            $monthData['total_profit'] = $monthData['profit_operation'] + $monthData['profit_asset'];
            
            // Calculate new total fields
            $monthData['revenue_total'] = $monthData['revenue_asset'] + $monthData['revenue_operation'];
            $monthData['expense_total'] = $monthData['expense_asset'] + $monthData['expense_operation'];
            
            // Calculate total value correction for all projects in this month
            $monthData['value_correction'] = 0;
            foreach ($projectKeys as $projectKey) {
                $monthData['value_correction'] += \App\Models\ValueCorrection::getCorrectionForMonth($projectKey, $month);
            }
            
            // Calculate total evaluation asset for all projects in this month
            $monthData['evaluation_asset'] = $monthData['expense_asset'] - $monthData['revenue_asset'] + $monthData['value_correction'];
            
            foreach ($summary['totals'] as $key => &$total) {
                $total += $monthData[$key];
            }
        }
        
        // Calculate cumulative cashflow for summary
        $this->calculateCumulativeCashflowSummary($summary, $allMonths);
        
        return $summary;
    }

    private function calculateCumulativeCashflowSummary(array &$summary, array $allMonths): void
    {
        $cumulativeCash = 0;
        $today = now()->startOfDay();
        
        // Sort months chronologically
        $sortedMonths = $allMonths;
        sort($sortedMonths);
        
        foreach ($sortedMonths as $month) {
            if (isset($summary['months'][$month])) {
                // Calculate net cash flow for this month (revenue - expense)
                $monthlyNetCash = $summary['months'][$month]['revenue_total'] - $summary['months'][$month]['expense_total'];
                
                // Add to cumulative cash
                $cumulativeCash += $monthlyNetCash;
                
                // Store cumulative cash for this month
                $summary['months'][$month]['cumulative_cash'] = $cumulativeCash;
                
                // Also store current cash (what we have today including future done transactions)
                $monthDate = \Carbon\Carbon::parse($month)->startOfDay();
                if ($monthDate->lte($today)) {
                    // This month is current or past - include in current cash
                    $summary['months'][$month]['current_cash'] = $cumulativeCash;
                } else {
                    // This month is future - show projected cash
                    $summary['months'][$month]['current_cash'] = $cumulativeCash;
                    $summary['months'][$month]['projected_cash'] = $cumulativeCash;
                }
            }
        }
        
        // Add cumulative_cash to totals structure (this includes all transactions)
        $summary['totals']['cumulative_cash'] = $cumulativeCash;
        $summary['totals']['current_cash'] = $cumulativeCash; // Show total including future done transactions
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

    protected static ?int $navigationSort = 1;

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
            'value_correction' => 'Value Correction',
            'evaluation_asset' => 'Evaluation Asset',
            'revenue_operation' => 'Revenue Operation',
            'revenue_asset' => 'Revenue Asset',
            'revenue_total' => 'Revenue Total',
            'expense_operation' => 'Expense Operation',
            'expense_asset' => 'Expense Asset',
            'expense_total' => 'Expense Total',
            'profit_operation' => 'Profit Operation',
            'profit_asset' => 'Profit Asset',
            'total_profit' => 'Total Profit',
            'cumulative_cash' => 'Cumulative Cashflow',
            'current_cash' => 'Current Cash Position',
            'projected_cash' => 'Projected Cash',
        ];
    }

    public function getMetricConfig(): array
    {
        return [
            'value_correction' => [
                'label' => 'Value Correction',
                'color' => 'purple',
                'icon' => 'heroicon-o-adjustments-horizontal',
                'description' => 'Manual value adjustments'
            ],
            'evaluation_asset' => [
                'label' => 'Evaluation Asset',
                'color' => 'indigo',
                'icon' => 'heroicon-o-building-office',
                'description' => 'Asset evaluation value'
            ],
            'revenue_operation' => [
                'label' => 'Revenue Operation',
                'color' => 'green',
                'icon' => 'heroicon-o-arrow-trending-up',
                'description' => 'Operational revenue'
            ],
            'revenue_asset' => [
                'label' => 'Revenue Asset',
                'color' => 'emerald',
                'icon' => 'heroicon-o-banknotes',
                'description' => 'Asset-based revenue'
            ],
            'revenue_total' => [
                'label' => 'Revenue Total',
                'color' => 'teal',
                'icon' => 'heroicon-o-currency-dollar',
                'description' => 'Total revenue (Operation + Asset)'
            ],
            'expense_operation' => [
                'label' => 'Expense Operation',
                'color' => 'red',
                'icon' => 'heroicon-o-arrow-trending-down',
                'description' => 'Operational expenses'
            ],
            'expense_asset' => [
                'label' => 'Expense Asset',
                'color' => 'rose',
                'icon' => 'heroicon-o-minus-circle',
                'description' => 'Asset-related expenses'
            ],
            'expense_total' => [
                'label' => 'Expense Total',
                'color' => 'pink',
                'icon' => 'heroicon-o-exclamation-triangle',
                'description' => 'Total expenses (Operation + Asset)'
            ],
            'profit_operation' => [
                'label' => 'Profit Operation',
                'color' => 'blue',
                'icon' => 'heroicon-o-chart-bar',
                'description' => 'Operational profit'
            ],
            'profit_asset' => [
                'label' => 'Profit Asset',
                'color' => 'cyan',
                'icon' => 'heroicon-o-presentation-chart-line',
                'description' => 'Asset-based profit'
            ],
            'total_profit' => [
                'label' => 'Total Profit',
                'color' => 'amber',
                'icon' => 'heroicon-o-trophy',
                'description' => 'Total profit (Operation + Asset)'
            ],
            'cumulative_cash' => [
                'label' => 'Cumulative Cashflow',
                'color' => 'orange',
                'icon' => 'heroicon-o-arrow-path',
                'description' => 'Running cash balance'
            ],
            'current_cash' => [
                'label' => 'Current Cash Position',
                'color' => 'lime',
                'icon' => 'heroicon-o-wallet',
                'description' => 'Current cash available'
            ],
            'projected_cash' => [
                'label' => 'Projected Cash',
                'color' => 'violet',
                'icon' => 'heroicon-o-eye',
                'description' => 'Future cash projection'
            ],
        ];
    }

    public function getMetricColorClasses(string $metricKey): array
    {
        $config = $this->getMetricConfig();
        $color = $config[$metricKey]['color'] ?? 'gray';
        
        // Use explicit color mappings to ensure Tailwind generates the classes
        $colorMap = [
            'green' => [
                'bg' => 'bg-green-100 dark:bg-green-900',
                'text' => 'text-green-800 dark:text-green-200',
                'border' => 'border-green-500 dark:border-green-400',
                'badge' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            ],
            'emerald' => [
                'bg' => 'bg-emerald-100 dark:bg-emerald-900',
                'text' => 'text-emerald-800 dark:text-emerald-200',
                'border' => 'border-emerald-500 dark:border-emerald-400',
                'badge' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
            ],
            'teal' => [
                'bg' => 'bg-teal-100 dark:bg-teal-900',
                'text' => 'text-teal-800 dark:text-teal-200',
                'border' => 'border-teal-500 dark:border-teal-400',
                'badge' => 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200',
            ],
            'red' => [
                'bg' => 'bg-red-100 dark:bg-red-900',
                'text' => 'text-red-800 dark:text-red-200',
                'border' => 'border-red-500 dark:border-red-400',
                'badge' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            ],
            'rose' => [
                'bg' => 'bg-rose-100 dark:bg-rose-900',
                'text' => 'text-rose-800 dark:text-rose-200',
                'border' => 'border-rose-500 dark:border-rose-400',
                'badge' => 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-200',
            ],
            'pink' => [
                'bg' => 'bg-pink-100 dark:bg-pink-900',
                'text' => 'text-pink-800 dark:text-pink-200',
                'border' => 'border-pink-500 dark:border-pink-400',
                'badge' => 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200',
            ],
            'blue' => [
                'bg' => 'bg-blue-100 dark:bg-blue-900',
                'text' => 'text-blue-800 dark:text-blue-200',
                'border' => 'border-blue-500 dark:border-blue-400',
                'badge' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            ],
            'cyan' => [
                'bg' => 'bg-cyan-100 dark:bg-cyan-900',
                'text' => 'text-cyan-800 dark:text-cyan-200',
                'border' => 'border-cyan-500 dark:border-cyan-400',
                'badge' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200',
            ],
            'amber' => [
                'bg' => 'bg-amber-100 dark:bg-amber-900',
                'text' => 'text-amber-800 dark:text-amber-200',
                'border' => 'border-amber-500 dark:border-amber-400',
                'badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
            ],
            'orange' => [
                'bg' => 'bg-orange-100 dark:bg-orange-900',
                'text' => 'text-orange-800 dark:text-orange-200',
                'border' => 'border-orange-500 dark:border-orange-400',
                'badge' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            ],
            'lime' => [
                'bg' => 'bg-lime-100 dark:bg-lime-900',
                'text' => 'text-lime-800 dark:text-lime-200',
                'border' => 'border-lime-500 dark:border-lime-400',
                'badge' => 'bg-lime-100 text-lime-800 dark:bg-lime-900 dark:text-lime-200',
            ],
            'violet' => [
                'bg' => 'bg-violet-100 dark:bg-violet-900',
                'text' => 'text-violet-800 dark:text-violet-200',
                'border' => 'border-violet-500 dark:border-violet-400',
                'badge' => 'bg-violet-100 text-violet-800 dark:bg-violet-900 dark:text-violet-200',
            ],
            'purple' => [
                'bg' => 'bg-purple-100 dark:bg-purple-900',
                'text' => 'text-purple-800 dark:text-purple-200',
                'border' => 'border-purple-500 dark:border-purple-400',
                'badge' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
            ],
            'indigo' => [
                'bg' => 'bg-indigo-100 dark:bg-indigo-900',
                'text' => 'text-indigo-800 dark:text-indigo-200',
                'border' => 'border-indigo-500 dark:border-indigo-400',
                'badge' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
            ],
        ];
        
        return $colorMap[$color] ?? $colorMap['gray'] ?? [
            'bg' => 'bg-gray-100 dark:bg-gray-900',
            'text' => 'text-gray-800 dark:text-gray-200',
            'border' => 'border-gray-500 dark:border-gray-400',
            'badge' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        ];
    }
}
