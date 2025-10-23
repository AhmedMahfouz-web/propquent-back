<?php

namespace App\Filament\Pages\Reports;

use Filament\Pages\Page;
use App\Models\User;
use App\Models\UserTransaction;
use App\Models\ProjectTransaction;
use App\Models\MonthlyProjectEvaluation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
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
use Illuminate\Support\Carbon;

class UserFinancialReport extends Page implements HasForms
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
    public $sortDirection = 'asc';

    #[Url]
    public $selectedMetrics = [];

    #[Url]
    public $sortBy = 'full_name';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.user-financial-report';
    protected static ?string $navigationGroup = 'Financial Reports';
    protected static ?string $title = 'User Financial Report';
    protected static ?int $navigationSort = 2;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exports([
                    ExcelExport::make()
                        ->fromTable()
                        ->withFilename(fn() => 'user-financial-report-' . date('Y-m-d'))
                        ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                ])
        ];
    }

    public function mount(): void
    {
        // Set default date range: current month to 12 months ago
        if (empty($this->startMonth)) {
            $this->startMonth = now()->subMonths(11)->format('Y-m-01'); // 12 months ago (including current)
        }
        if (empty($this->endMonth)) {
            $this->endMonth = now()->format('Y-m-01'); // Current month
        }

        // Default to all metrics if none selected
        if (empty($this->selectedMetrics)) {
            $this->selectedMetrics = array_keys($this->getAvailableMetrics());
        }

        $this->form->fill([
            'search' => $this->search,
            'startMonth' => $this->startMonth,
            'endMonth' => $this->endMonth,
            'selectedMetrics' => $this->selectedMetrics,
            'sortBy' => $this->sortBy,
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
                        TextInput::make('search')
                            ->label('Search Users')
                            ->placeholder('Search by name or custom ID...')
                            ->live(debounce: 500),
                        Select::make('startMonth')
                            ->label('Start Month')
                            ->options($monthOptions)
                            ->live(),
                        Select::make('endMonth')
                            ->label('End Month')
                            ->options($monthOptions)
                            ->live(),
                        Select::make('perPage')
                            ->label('Items Per Page')
                            ->options([10 => 10, 25 => 25, 50 => 50, 'all' => 'All'])
                            ->live(),
                        Select::make('selectedMetrics')
                            ->label('Show Metrics')
                            ->options($metricOptions)
                            ->multiple()
                            ->default(array_keys($metricOptions))
                            ->columnSpan(2)
                            ->live(),
                        Select::make('sortBy')
                            ->label('Sort By')
                            ->options([
                                'full_name' => 'User Name',
                                'custom_id' => 'User ID',
                                'total_deposits' => 'Total Deposits',
                                'total_equity' => 'Total Equity',
                                'total_profit' => 'Total Profit',
                            ])
                            ->live(),
                    ]),
            ]);
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function updated($property): void
    {
        if (in_array(str_replace('data.', '', $property), ['search', 'startMonth', 'endMonth', 'selectedMetrics', 'sortBy', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function getAvailableMonthsProperty(): array
    {
        $today = now()->format('Y-m-d');


        // Use same logic as CompanyFinancialReport - only done transactions with transaction_date
        $projectMonths = ProjectTransaction::where('status', 'done')
            ->select(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01") as month_date'))
            ->whereNotNull('transaction_date')
            ->distinct();

        $userMonths = UserTransaction::where('status', UserTransaction::STATUS_DONE)
            ->select(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01") as month_date'))
            ->whereNotNull('transaction_date')
            ->distinct();

        $months = $projectMonths->union($userMonths)
            ->orderBy('month_date', 'asc')
            ->pluck('month_date')
            ->toArray();

        return array_combine($months, array_map(fn($m) => date('M Y', strtotime($m)), $months));
    }

    private function getAvailableMetrics(): array
    {
        return [
            'deposits' => 'Deposits',
            'withdrawals' => 'Withdrawals',
            'equity' => 'Equity',
            'equity_percentage' => 'Equity %',
            'profit_asset' => 'Profit Asset',
            'profit_operation' => 'Profit Operation',
            'total_profit' => 'Total Profit',
        ];
    }

    public function getMetricConfig(): array
    {
        return [
            'deposits' => [
                'label' => 'Deposits',
                'color' => 'green',
                'icon' => 'heroicon-o-arrow-down-on-square',
                'description' => 'Money deposited by investor'
            ],
            'withdrawals' => [
                'label' => 'Withdrawals',
                'color' => 'red',
                'icon' => 'heroicon-o-arrow-up-on-square',
                'description' => 'Money withdrawn by investor'
            ],
            'equity' => [
                'label' => 'Equity',
                'color' => 'blue',
                'icon' => 'heroicon-o-scale',
                'description' => 'Investor equity position'
            ],
            'equity_percentage' => [
                'label' => 'Equity %',
                'color' => 'purple',
                'icon' => 'heroicon-o-chart-pie',
                'description' => 'Percentage of total equity'
            ],
            'profit_asset' => [
                'label' => 'Profit Asset',
                'color' => 'cyan',
                'icon' => 'heroicon-o-building-office',
                'description' => 'Profit from asset investments'
            ],
            'profit_operation' => [
                'label' => 'Profit Operation',
                'color' => 'indigo',
                'icon' => 'heroicon-o-cog-6-tooth',
                'description' => 'Profit from operations'
            ],
            'total_profit' => [
                'label' => 'Total Profit',
                'color' => 'amber',
                'icon' => 'heroicon-o-trophy',
                'description' => 'Total profit earned'
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
            'red' => [
                'bg' => 'bg-red-100 dark:bg-red-900',
                'text' => 'text-red-800 dark:text-red-200',
                'border' => 'border-red-500 dark:border-red-400',
                'badge' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            ],
            'blue' => [
                'bg' => 'bg-blue-100 dark:bg-blue-900',
                'text' => 'text-blue-800 dark:text-blue-200',
                'border' => 'border-blue-500 dark:border-blue-400',
                'badge' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            ],
            'purple' => [
                'bg' => 'bg-purple-100 dark:bg-purple-900',
                'text' => 'text-purple-800 dark:text-purple-200',
                'border' => 'border-purple-500 dark:border-purple-400',
                'badge' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
            ],
            'amber' => [
                'bg' => 'bg-amber-100 dark:bg-amber-900',
                'text' => 'text-amber-800 dark:text-amber-200',
                'border' => 'border-amber-500 dark:border-amber-400',
                'badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
            ],
            'cyan' => [
                'bg' => 'bg-cyan-100 dark:bg-cyan-900',
                'text' => 'text-cyan-800 dark:text-cyan-200',
                'border' => 'border-cyan-500 dark:border-cyan-400',
                'badge' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200',
            ],
            'indigo' => [
                'bg' => 'bg-indigo-100 dark:bg-indigo-900',
                'text' => 'text-indigo-800 dark:text-indigo-200',
                'border' => 'border-indigo-500 dark:border-indigo-400',
                'badge' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
            ],
        ];

        return $colorMap[$color] ?? [
            'bg' => 'bg-gray-100 dark:bg-gray-900',
            'text' => 'text-gray-800 dark:text-gray-200',
            'border' => 'border-gray-500 dark:border-gray-400',
            'badge' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        ];
    }

    public function getMetricTotalColorClasses(string $metricKey): array
    {
        $config = $this->getMetricConfig();
        $color = $config[$metricKey]['color'] ?? 'gray';

        // Only text colors for totals (no background)
        $colorMap = [
            'green' => [
                'bg' => '',
                'text' => 'text-green-700 dark:text-green-400',
            ],
            'red' => [
                'bg' => '',
                'text' => 'text-red-700 dark:text-red-400',
            ],
            'blue' => [
                'bg' => '',
                'text' => 'text-blue-700 dark:text-blue-400',
            ],
            'purple' => [
                'bg' => '',
                'text' => 'text-purple-700 dark:text-purple-400',
            ],
            'amber' => [
                'bg' => '',
                'text' => 'text-amber-700 dark:text-amber-400',
            ],
            'cyan' => [
                'bg' => '',
                'text' => 'text-cyan-700 dark:text-cyan-400',
            ],
            'indigo' => [
                'bg' => '',
                'text' => 'text-indigo-700 dark:text-indigo-400',
            ],
        ];

        return $colorMap[$color] ?? [
            'bg' => '',
            'text' => 'text-gray-700 dark:text-gray-400',
        ];
    }

    public function sortByField($field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public $debugInfo = [];

    #[Computed]
    public function reportData(): array
    {
        if (!$this->readyToLoad) {
            return [
                'users' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage),
                'userFinancialData' => [],
                'allMonths' => [],
                'companyData' => [],
            ];
        }

        $allMonths = $this->getMonthsInRange();
        $companyData = $this->calculateCompanyFinancialData($allMonths);

        // Optimized user query - separate search from financial data calculation
        $usersQuery = $this->buildOptimizedUserQuery($allMonths);
        $users = $usersQuery->paginate($this->perPage === 'all' ? 1000 : $this->perPage)->withQueryString();

        // Calculate user financial data
        $userFinancialData = [];
        foreach ($users as $user) {
            $userFinancialData[$user->id] = $this->calculateUserFinancialData($user, $allMonths, $companyData);
        }

        // Calculate company profit for each month
        $companyProfitByMonth = $this->calculateCompanyProfitByMonth($allMonths);

        // Calculate total company equity from company data
        $totalCompanyEquity = $this->calculateCompanyTotalEquity($allMonths, $companyData);

        // Update equity percentages and user profits using total company equity
        $this->updateEquityPercentagesAndProfits($userFinancialData, $allMonths, $companyProfitByMonth, $totalCompanyEquity);

        // Apply financial sorting if needed
        if (in_array($this->sortBy, ['total_deposits', 'total_equity', 'total_profit'])) {
            $sortedUsers = $users->getCollection()->sortBy(function ($user) use ($userFinancialData) {
                switch ($this->sortBy) {
                    case 'total_deposits':
                        return array_sum($userFinancialData[$user->id]['deposits'] ?? []);
                    case 'total_equity':
                        // For equity, use the most recent month's value instead of sum
                        $equityData = $userFinancialData[$user->id]['equity'] ?? [];
                        return !empty($equityData) ? reset($equityData) : 0; // First value (most recent month)
                    case 'total_profit':
                        return array_sum($userFinancialData[$user->id]['total_profit'] ?? []);
                    default:
                        return 0;
                }
            }, SORT_REGULAR, $this->sortDirection === 'desc');

            $users->setCollection($sortedUsers);
        }

        return [
            'users' => $users,
            'userFinancialData' => $userFinancialData,
            'allMonths' => $allMonths,
            'companyData' => $companyData,
            'debugInfo' => $this->debugInfo,
        ];
    }

    private function getMonthsInRange(): array
    {
        if (empty($this->startMonth) || empty($this->endMonth)) {
            // If no months selected, get all available months
            $availableMonths = $this->getAvailableMonthsProperty();
            return array_keys($availableMonths);
        }

        $start = new \DateTime($this->startMonth);
        $end = new \DateTime($this->endMonth);
        $interval = new \DateInterval('P1M');
        $period = new \DatePeriod($start, $interval, $end->modify('+1 month'));

        $months = [];
        foreach ($period as $dt) {
            $months[] = $dt->format('Y-m-01');
        }

        return array_reverse($months); // Show latest first
    }

    private function calculateCompanyFinancialData(array $monthsToShow): array
    {
        // This is the same logic from the original blade file for company financial calculations
        $reportData = ['revenue' => [], 'expense' => []];
        $monthlyTotals = ['revenue' => [], 'expense' => []];

        if (empty($monthsToShow)) {
            return compact('reportData', 'monthlyTotals');
        }

        // Initialize totals for all months
        foreach ($monthsToShow as $month) {
            $monthlyTotals['revenue'][$month] = 0;
            $monthlyTotals['expense'][$month] = 0;
        }


        $projectTransactions = DB::table('project_transactions as pt')
            ->select(
                DB::raw("DATE_FORMAT(pt.transaction_date, '%Y-%m-01') as month_date"),
                'pt.financial_type as type',
                'pt.serving as serving_name',
                DB::raw('SUM(pt.amount) as total_amount'),
            )
            ->where('pt.status', 'done')
            ->whereBetween('pt.transaction_date', [
                $monthsToShow[0], // Oldest month (first in array)
                Carbon::parse(end($monthsToShow))->endOfMonth(), // Newest month (last in array)
            ])
            ->groupBy('month_date', 'pt.financial_type', 'pt.serving')
            ->orderBy('month_date', 'desc')
            ->cursor();

        foreach ($projectTransactions as $transaction) {
            $type = strtolower($transaction->type);
            if ($type !== 'revenue' && $type !== 'expense') {
                continue;
            }

            $servingName = $transaction->serving_name;
            $month = $transaction->month_date;

            if (!in_array($month, $monthsToShow)) {
                continue;
            }

            if (!isset($reportData[$type][$servingName])) {
                foreach ($monthsToShow as $m) {
                    $reportData[$type][$servingName][$m] = 0;
                }
            }

            $reportData[$type][$servingName][$month] = $transaction->total_amount;
            $monthlyTotals[$type][$month] += $transaction->total_amount;
        }

        // Debug: Add monthlyTotals to debug info
        $this->debugInfo['monthly_totals'] = $monthlyTotals;

        return compact('reportData', 'monthlyTotals');
    }

    private function calculateUserFinancialData($user, array $monthsToShow, array $companyData): array
    {
        $userData = [
            'full_name' => $user->full_name,
            'custom_id' => $user->custom_id,
            'deposits' => [],
            'withdrawals' => [],
            'equity' => [],
            'equity_percentage' => [],
            'profit_asset' => [],
            'profit_operation' => [],
            'total_profit' => [],
        ];

        // Initialize all months
        foreach ($monthsToShow as $month) {
            $userData['deposits'][$month] = 0;
            $userData['withdrawals'][$month] = 0;
            $userData['equity'][$month] = 0;
            $userData['equity_percentage'][$month] = 0;
            $userData['profit_asset'][$month] = 0;
            $userData['profit_operation'][$month] = 0;
            $userData['total_profit'][$month] = 0;
        }

        // Get user transactions - simplified approach matching company report
        $userTransactionsData = UserTransaction::query()
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m-01') as month_date"),
                DB::raw("SUM(CASE WHEN transaction_type = '" . UserTransaction::TYPE_DEPOSIT . "' THEN amount ELSE 0 END) as deposits"),
                DB::raw("SUM(CASE WHEN transaction_type = '" . UserTransaction::TYPE_WITHDRAWAL . "' THEN amount ELSE 0 END) as withdrawals"),
            )
            ->where('user_id', $user->id)
            ->where('status', UserTransaction::STATUS_DONE)
            ->groupBy('month_date')
            ->get()
            ->keyBy('month_date');

        // Calculate company total equity for each month (cash + evaluation)
        $companyTotalEquity = $this->calculateCompanyTotalEquity($monthsToShow, $companyData);
        
        // We'll calculate total users equity after processing all users
        $totalUsersEquity = [];

        // Calculate equity and profits (simplified version)
        $previousEquity = 0;
        // $monthsToShow = UserTransaction::query()
        //     ->select(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01") as month_date'))
        //     ->distinct()
        //     ->orderBy('month_date', 'asc')
        //     ->pluck('month_date')
        //     ->toArray();
        // foreach ($userTransactionsData as $transaction) {
        //     $month = $transaction->month_date;
        //     if ($monthsToShow->contains($month)) {
        //         $userData['deposits'][$month] = $transaction->deposits;
        //         $userData['withdrawals'][$month] = $transaction->withdrawals;
        //         $userData['net'][$month] = $transaction->total_deposits - $transaction->total_withdrawals;
        //     }
        // }
        // Process months in chronological order (oldest first) for proper equity calculation
        // Since $monthsToShow is newest first, reverse it

        foreach (array_reverse($monthsToShow) as $month) {
            $deposits = $userTransactionsData[$month]->deposits ?? 0;
            $withdrawals = $userTransactionsData[$month]->withdrawals ?? 0;

            $userData['deposits'][$month] = $deposits;
            $userData['withdrawals'][$month] = $withdrawals;

            // Calculate equity: previous equity + deposits - withdrawals
            $userData['equity'][$month] = $previousEquity + $deposits - $withdrawals;

            // Equity percentage will be calculated later after all users are processed
            $userData['equity_percentage'][$month] = 0;

            $previousEquity = $userData['equity'][$month];
        }

        return $userData;
    }

    /**
     * Calculate company profit for each month (sum of all projects' profit)
     */
    private function calculateCompanyProfitByMonth(array $allMonths): array
    {
        $companyProfitByMonth = [];
        
        foreach ($allMonths as $month) {
            // Get all projects' total profit for this month using the same calculation as Project Financial Report
            $monthlyProjectEvaluations = \App\Models\MonthlyProjectEvaluation::where('month_date', $month)->get();
            
            $totalCompanyProfit = 0;
            
            foreach ($monthlyProjectEvaluations as $evaluation) {
                // Calculate profit operation for this project this month
                $profitOperation = (float) $evaluation->profit_operation;
                
                // Calculate profit asset for this project this month using the formula
                // We need to get the previous month's asset evaluation for this project
                $previousMonthEvaluation = \App\Models\MonthlyProjectEvaluation::where('project_key', $evaluation->project_key)
                    ->where('month_date', '<', $month)
                    ->orderBy('month_date', 'desc')
                    ->first();
                    
                $previousAssetEvaluation = $previousMonthEvaluation ? (float) $previousMonthEvaluation->asset_evaluation : 0;
                $currentAssetEvaluation = (float) $evaluation->asset_evaluation;
                $revenueAsset = (float) $evaluation->revenue_asset;
                $expenseAsset = (float) $evaluation->expense_asset;
                
                $profitAsset = $currentAssetEvaluation - $previousAssetEvaluation + $revenueAsset - $expenseAsset;
                
                // Total project profit = profit operation + profit asset
                $projectTotalProfit = $profitOperation + $profitAsset;
                $totalCompanyProfit += $projectTotalProfit;
            }
            
            $companyProfitByMonth[$month] = $totalCompanyProfit;
        }
        
        return $companyProfitByMonth;
    }

    /**
     * Update equity percentages and calculate user profits based on previous month equity percentage
     */
    private function updateEquityPercentagesAndProfits(array &$userFinancialData, array $allMonths, array $companyProfitByMonth, array $totalCompanyEquity): void
    {
        // Convert months to chronological order (oldest first) for proper calculation
        $monthsChronological = array_reverse($allMonths);
        
        // Update equity percentages using total company equity
        foreach ($userFinancialData as $userId => &$userData) {
            foreach ($allMonths as $month) {
                $userEquity = $userData['equity'][$month] ?? 0;
                $companyEquity = $totalCompanyEquity[$month] ?? 0;
                
                // Calculate equity percentage: user's equity / total company equity * 100
                if ($companyEquity > 0) {
                    $userData['equity_percentage'][$month] = ($userEquity / $companyEquity) * 100;
                } else {
                    $userData['equity_percentage'][$month] = 0;
                }
            }
        }
        
        // Now calculate user profits using previous month's equity percentage
        foreach ($userFinancialData as $userId => &$userData) {
            $previousMonth = null;
            
            foreach ($monthsChronological as $month) {
                $companyProfitThisMonth = $companyProfitByMonth[$month] ?? 0;
                
                // Get previous month's equity percentage (0 if no previous month)
                $previousEquityPercentage = 0;
                if ($previousMonth && isset($userData['equity_percentage'][$previousMonth])) {
                    $previousEquityPercentage = $userData['equity_percentage'][$previousMonth];
                }
                
                // Simple calculation: Equity % of user (previous month) * Company profit (this month)
                $userTotalProfit = ($previousEquityPercentage / 100) * $companyProfitThisMonth;
                
                // Set all profit values
                $userData['profit_asset'][$month] = $userTotalProfit * 0.5;
                $userData['profit_operation'][$month] = $userTotalProfit * 0.5;
                $userData['total_profit'][$month] = $userTotalProfit;

                // Debug logging
                $this->debugInfo['profit_calculations'][$month][$userId] = [
                    'previous_month' => $previousMonth,
                    'previous_equity_percentage' => $previousEquityPercentage,
                    'company_profit_this_month' => $companyProfitThisMonth,
                    'user_total_profit' => $userTotalProfit,
                ];
                
                $previousMonth = $month;
            }
        }
    }

    /**
     * Calculate company total equity for each month (cash + evaluation)
     */
    private function calculateCompanyTotalEquity(array $monthsToShow, array $companyData): array
    {
        $reportData = $companyData['reportData'];
        $monthlyTotals = $companyData['monthlyTotals'];

        // Calculate user financials (deposits/withdrawals)
        $userFinancials = ['deposits' => [], 'withdrawals' => [], 'net' => []];

        // Initialize all months with zero
        foreach ($monthsToShow as $month) {
            $userFinancials['deposits'][$month] = 0;
            $userFinancials['withdrawals'][$month] = 0;
            $userFinancials['net'][$month] = 0;
        }

        // Debug: Log the query parameters
        $startDate = $monthsToShow[0];
        $endDate = Carbon::parse(end($monthsToShow))->endOfMonth();
        
        $this->debugInfo['user_transactions_query_params'] = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'deposit_type' => UserTransaction::TYPE_DEPOSIT,
            'withdrawal_type' => UserTransaction::TYPE_WITHDRAWAL,
            'status_done' => UserTransaction::STATUS_DONE
        ];

        $userTransactions = UserTransaction::query()
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m-01') as month_date"),
                DB::raw("SUM(CASE WHEN transaction_type = '" . UserTransaction::TYPE_DEPOSIT . "' THEN amount ELSE 0 END) as total_deposits"),
                DB::raw("SUM(CASE WHEN transaction_type = '" . UserTransaction::TYPE_WITHDRAWAL . "' THEN amount ELSE 0 END) as total_withdrawals"),
            )
            ->where('status', UserTransaction::STATUS_DONE)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->groupBy('month_date')
            ->get();

        // Debug: Also check total user transactions without date filter
        $totalUserTransactions = UserTransaction::where('status', UserTransaction::STATUS_DONE)->count();
        $this->debugInfo['total_user_transactions_in_db'] = $totalUserTransactions;


        // Debug: Log user transactions query results
        $this->debugInfo['user_transactions_query'] = [
            'total_found' => $userTransactions->count(),
            'months_to_show' => $monthsToShow,
            'transactions' => $userTransactions->toArray()
        ];

        foreach ($userTransactions as $transaction) {
            $month = $transaction->month_date;
            if (in_array($month, $monthsToShow)) {
                $userFinancials['deposits'][$month] = $transaction->total_deposits;
                $userFinancials['withdrawals'][$month] = $transaction->total_withdrawals;
                $userFinancials['net'][$month] = $transaction->total_deposits - $transaction->total_withdrawals;
            }
        }

        // Debug: Log final user financials
        $this->debugInfo['user_financials_calculated'] = $userFinancials;

        // Calculate Evaluation (Expense - Revenue for each serving)
        $evaluation = ['asset' => [], 'operation' => [], 'total' => []];

        // Initialize evaluation arrays
        foreach ($monthsToShow as $month) {
            $evaluation['asset'][$month] = 0;
            $evaluation['operation'][$month] = 0;
            $evaluation['total'][$month] = 0;
        }

        // Calculate evaluation for each serving
        foreach (['asset', 'operation'] as $serving) {
            foreach ($monthsToShow as $month) {
                $expense = $reportData['expense'][$serving][$month] ?? 0;
                $revenue = $reportData['revenue'][$serving][$month] ?? 0;
                $evaluation[$serving][$month] = $expense - $revenue;
                $evaluation['total'][$month] += $evaluation[$serving][$month];
            }
        }

        // Calculate Cash using MonthlyProjectEvaluation data (same as company profit calculation)
        $cash = [];
        $previousMonthCash = 0;

        // Process months in chronological order (oldest first)
        foreach (array_reverse($monthsToShow) as $month) {
            // Get revenue and expense from MonthlyProjectEvaluation (same source as company profit)
            $monthlyData = MonthlyProjectEvaluation::where('month_date', $month)
                ->selectRaw('
                    SUM(revenue_asset + revenue_operation) as total_revenue,
                    SUM(expense_asset + expense_operation) as total_expense
                ')
                ->first();
                
            $revenue = $monthlyData ? (float) $monthlyData->total_revenue : 0;
            $expense = $monthlyData ? (float) $monthlyData->total_expense : 0;
            $deposits = $userFinancials['deposits'][$month] ?? 0;
            $withdrawals = $userFinancials['withdrawals'][$month] ?? 0;

            // Same formula as Company Financial Report
            $cash[$month] = $previousMonthCash + $deposits + $revenue - $withdrawals - $expense;
            $previousMonthCash = $cash[$month];
            
            // Debug: Log cash calculation for each month
            $this->debugInfo['cash_calculations'][$month] = [
                'previous_month_cash' => $previousMonthCash - $deposits - $revenue + $withdrawals + $expense,
                'deposits' => $deposits,
                'revenue' => $revenue,
                'withdrawals' => $withdrawals,
                'expense' => $expense,
                'calculated_cash' => $cash[$month]
            ];
        }

        // Calculate Total Company Equity (Cash + Asset Evaluation)
        // This should match the company financial data calculation
        $equityTotal = [];
        
        // Get asset evaluation from MonthlyProjectEvaluation for each month
        foreach ($monthsToShow as $month) {
            $cashAmount = $cash[$month] ?? 0;
            
            // Get total asset evaluation for this month from all projects
            $assetEvaluation = MonthlyProjectEvaluation::where('month_date', $month)
                ->sum('asset_evaluation');
            
            $equityTotal[$month] = $cashAmount + $assetEvaluation;
            
            // Debug logging
            $this->debugInfo['company_equity_calculations'][$month] = [
                'cash' => $cashAmount,
                'asset_evaluation' => $assetEvaluation,
                'total_equity' => $equityTotal[$month]
            ];
        }

        return $equityTotal;
    }

    /**
     * Build optimized user query with performance improvements
     */
    private function buildOptimizedUserQuery(array $allMonths)
    {
        // Start with a simpler base query
        $usersQuery = User::query()
            ->select('users.id', 'users.full_name', 'users.custom_id');

        // Apply search filter first (most selective)
        if ($this->search) {
            $search = trim($this->search);

            // Optimize search: try exact matches first, then partial matches
            $usersQuery->where(function ($q) use ($search) {
                // Exact custom_id match (fastest)
                $q->where('users.custom_id', '=', $search)
                    // Exact name match
                    ->orWhere('users.full_name', '=', $search)
                    // Prefix matches (can use indexes)
                    ->orWhere('users.full_name', 'like', $search . '%')
                    ->orWhere('users.custom_id', 'like', $search . '%')
                    // Fallback to full wildcard search
                    ->orWhere('users.full_name', 'like', '%' . $search . '%')
                    ->orWhere('users.custom_id', 'like', '%' . $search . '%');
            });
        }

        // No longer filter by transaction existence - show all users

        // Optimized sorting
        switch ($this->sortBy) {
            case 'custom_id':
                // Pre-calculate numeric part for better performance
                $usersQuery->orderByRaw('CAST(SUBSTRING(users.custom_id, 5) AS UNSIGNED) ' . $this->sortDirection);
                break;
            case 'full_name':
                $usersQuery->orderBy('users.full_name', $this->sortDirection);
                break;
            default:
                $usersQuery->orderBy('users.full_name', $this->sortDirection);
                break;
        }

        return $usersQuery;
    }
}
