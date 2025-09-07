<?php

namespace App\Filament\Pages\Reports;

use Filament\Pages\Page;
use App\Models\User;
use App\Models\UserTransaction;
use App\Models\ProjectTransaction;
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
        $projectMonths = ProjectTransaction::select(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01") as month_date'))
            ->distinct();

        $userMonths = UserTransaction::select(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m-01") as month_date'))
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
            'total_profit' => 'Total Profit',
            'profit_asset' => 'Profit Asset',
            'profit_operation' => 'Profit Operation',
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

        // Apply financial sorting if needed
        if (in_array($this->sortBy, ['total_deposits', 'total_equity', 'total_profit'])) {
            $sortedUsers = $users->getCollection()->sortBy(function ($user) use ($userFinancialData) {
                switch ($this->sortBy) {
                    case 'total_deposits':
                        return array_sum($userFinancialData[$user->id]['deposits'] ?? []);
                    case 'total_equity':
                        return array_sum($userFinancialData[$user->id]['equity'] ?? []);
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
            ->whereBetween('pt.transaction_date', [
                end($monthsToShow),
                Carbon::parse($monthsToShow[0])->endOfMonth(),
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
            'total_profit' => [],
            'profit_asset' => [],
            'profit_operation' => [],
        ];

        // Initialize all months
        foreach ($monthsToShow as $month) {
            $userData['deposits'][$month] = 0;
            $userData['withdrawals'][$month] = 0;
            $userData['equity'][$month] = 0;
            $userData['equity_percentage'][$month] = 0;
            $userData['total_profit'][$month] = 0;
            $userData['profit_asset'][$month] = 0;
            $userData['profit_operation'][$month] = 0;
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


        // Calculate equity and profits (simplified version)
        $previousEquity = 0;
        $previousEquityPercentage = 0;
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

        foreach ($monthsToShow as $month) {
            $deposits = $userTransactionsData[$month]->deposits ?? 0;
            $withdrawals = $userTransactionsData[$month]->withdrawals ?? 0;

            $userData['deposits'][$month] = $deposits;
            $userData['withdrawals'][$month] = $withdrawals;

            // Calculate equity: previous equity + deposits - withdrawals
            $userData['equity'][$month] = $previousEquity + $deposits - $withdrawals;
            $userData['equity_percentage'][$month] = $previousEquityPercentage; // Simplified

            $previousEquity = $userData['equity'][$month];
        }

        return $userData;
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
