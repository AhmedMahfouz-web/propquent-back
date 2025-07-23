# Design Document

## Overview

This design document outlines the implementation of two specific report pages for the real estate project management platform:

1. **User Investment Report**: A report that tracks user deposits and withdrawals, calculates net deposits each month, and identifies active investors based on their net deposit history.

2. **Project Transaction Report**: A report that shows total revenue and expense transactions for each project, categorized by transaction type, on a monthly basis.

These reports will provide clear visibility into both user investment patterns and project financial performance, enabling better decision-making and financial management.

## Architecture

### System Architecture Overview

The reporting system will integrate with the existing Laravel-Filament architecture:

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                        │
├─────────────────────────────────────────────────────────────┤
│  User Investment Report  │  Project Transaction Report      │
├─────────────────────────────────────────────────────────────┤
│                    Business Logic Layer                     │
├─────────────────────────────────────────────────────────────┤
│ Investment Service │ Transaction Service │ Report Service   │
├─────────────────────────────────────────────────────────────┤
│                    Data Access Layer                        │
├─────────────────────────────────────────────────────────────┤
│    Eloquent Models    │    Repositories    │   Caching      │
├─────────────────────────────────────────────────────────────┤
│                    Database Layer                           │
├─────────────────────────────────────────────────────────────┤
│  Users │ UserTransactions │ Projects │ ProjectTransactions  │
└─────────────────────────────────────────────────────────────┘
```

### Key Architectural Principles

1. **Service-Based Design**: Business logic encapsulated in dedicated service classes
2. **Repository Pattern**: Data access abstracted through repository interfaces
3. **Caching Strategy**: Performance optimization through strategic caching of calculations and reports

## Components and Interfaces

### 1. User Investment Report

#### Report Page Structure

```php
class UserInvestmentReportPage extends Filament\Pages\Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string $view = 'filament.pages.reports.user-investment-report';

    public function mount(): void
    {
        $this->form->fill([
            'date_range' => [
                'from' => now()->startOfMonth()->subMonths(11)->toDateString(),
                'until' => now()->endOfMonth()->toDateString(),
            ],
            'user_id' => null,
        ]);

        $this->loadReportData();
    }

    protected function loadReportData(): void
    {
        $filters = $this->form->getState();
        $this->reportData = app(InvestmentReportService::class)
            ->generateUserInvestmentReport($filters);
    }
}
```

#### Investment Report Service

```php
class InvestmentReportService
{
    public function __construct(
        private UserTransactionRepository $transactionRepository,
        private CacheManager $cache
    ) {}

    public function generateUserInvestmentReport(array $filters): array
    {
        $cacheKey = 'user_investment_report_' . md5(serialize($filters));

        return $this->cache->remember($cacheKey, 3600, function () use ($filters) {
            $query = $this->transactionRepository->getBaseQuery();

            // Apply filters
            if (!empty($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }

            if (!empty($filters['date_range'])) {
                $query->whereBetween('transaction_date', [
                    $filters['date_range']['from'],
                    $filters['date_range']['until'],
                ]);
            }

            $transactions = $query->get();

            // Group by month
            $monthlyData = $this->calculateMonthlyData($transactions);

            // Calculate active investors
            $activeInvestors = $this->identifyActiveInvestors($transactions);

            return [
                'monthly_data' => $monthlyData,
                'active_investors' => $activeInvestors,
                'summary' => $this->calculateSummary($monthlyData),
            ];
        });
    }

    private function calculateMonthlyData(Collection $transactions): Collection
    {
        return $transactions
            ->groupBy(function ($transaction) {
                return $transaction->transaction_date->format('Y-m');
            })
            ->map(function ($monthTransactions) {
                $deposits = $monthTransactions
                    ->where('transaction_type', 'deposit')
                    ->sum('amount');

                $withdrawals = $monthTransactions
                    ->where('transaction_type', 'withdrawal')
                    ->sum('amount');

                return [
                    'deposits' => $deposits,
                    'withdrawals' => $withdrawals,
                    'net_deposits' => $deposits - $withdrawals,
                ];
            });
    }

    private function identifyActiveInvestors(Collection $transactions): Collection
    {
        $investorData = $transactions
            ->groupBy('user_id')
            ->map(function ($userTransactions) {
                $monthlyData = $this->calculateMonthlyData($userTransactions);
                $cumulativeNetDeposit = 0;

                foreach ($monthlyData as $month => $data) {
                    $cumulativeNetDeposit += $data['net_deposits'];
                }

                return [
                    'user_id' => $userTransactions->first()->user_id,
                    'user_name' => $userTransactions->first()->user->name,
                    'cumulative_net_deposit' => $cumulativeNetDeposit,
                    'is_active' => $cumulativeNetDeposit > 0,
                ];
            });

        return $investorData;
    }
}
```

#### User Investment Report View

```blade
<x-filament::page>
    <x-filament::card>
        <form wire:submit.prevent="loadReportData">
            {{ $this->form }}

            <x-filament::button type="submit" class="mt-4">
                Generate Report
            </x-filament::button>
        </form>
    </x-filament::card>

    @if($reportData)
        <x-filament::card class="mt-6">
            <h2 class="text-xl font-bold mb-4">Monthly Investment Summary</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Month</th>
                            <th scope="col" class="px-6 py-3">Total Deposits</th>
                            <th scope="col" class="px-6 py-3">Total Withdrawals</th>
                            <th scope="col" class="px-6 py-3">Net Deposits</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['monthly_data'] as $month => $data)
                            <tr class="bg-white border-b">
                                <td class="px-6 py-4">{{ $month }}</td>
                                <td class="px-6 py-4">{{ number_format($data['deposits'], 2) }}</td>
                                <td class="px-6 py-4">{{ number_format($data['withdrawals'], 2) }}</td>
                                <td class="px-6 py-4 {{ $data['net_deposits'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($data['net_deposits'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <h3 class="text-lg font-semibold mb-2">Active Investors</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($reportData['active_investors'] as $investor)
                        <div class="p-4 border rounded-lg {{ $investor['is_active'] ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                            <p class="font-medium">{{ $investor['user_name'] }}</p>
                            <p class="text-sm">
                                Net Deposit: {{ number_format($investor['cumulative_net_deposit'], 2) }}
                            </p>
                            <p class="text-xs mt-1">
                                Status: {{ $investor['is_active'] ? 'Active Investor' : 'Inactive' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-6">
                <x-filament::button wire:click="exportReport('csv')">
                    Export CSV
                </x-filament::button>
                <x-filament::button wire:click="exportReport('pdf')" class="ml-2">
                    Export PDF
                </x-filament::button>
            </div>
        </x-filament::card>
    @endif
</x-filament::page>
```

### 2. Project Transaction Report

#### Report Page Structure

```php
class ProjectTransactionReportPage extends Filament\Pages\Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-report';
    protected static string $view = 'filament.pages.reports.project-transaction-report';

    public function mount(): void
    {
        $this->form->fill([
            'date_range' => [
                'from' => now()->startOfMonth()->subMonths(11)->toDateString(),
                'until' => now()->endOfMonth()->toDateString(),
            ],
            'project_id' => null,
        ]);

        $this->loadReportData();
    }

    protected function loadReportData(): void
    {
        $filters = $this->form->getState();
        $this->reportData = app(ProjectTransactionReportService::class)
            ->generateProjectTransactionReport($filters);
    }
}
```

#### Project Transaction Report Service

```php
class ProjectTransactionReportService
{
    public function __construct(
        private ProjectTransactionRepository $transactionRepository,
        private CacheManager $cache
    ) {}

    public function generateProjectTransactionReport(array $filters): array
    {
        $cacheKey = 'project_transaction_report_' . md5(serialize($filters));

        return $this->cache->remember($cacheKey, 3600, function () use ($filters) {
            $query = $this->transactionRepository->getBaseQuery();

            // Apply filters
            if (!empty($filters['project_id'])) {
                $query->where('project_id', $filters['project_id']);
            }

            if (!empty($filters['date_range'])) {
                $query->whereBetween('transaction_date', [
                    $filters['date_range']['from'],
                    $filters['date_range']['until'],
                ]);
            }

            $transactions = $query->get();

            // Group by month and category
            $monthlyData = $this->calculateMonthlyData($transactions);

            return [
                'monthly_data' => $monthlyData,
                'summary' => $this->calculateSummary($monthlyData),
                'category_totals' => $this->calculateCategoryTotals($transactions),
            ];
        });
    }

    private function calculateMonthlyData(Collection $transactions): Collection
    {
        return $transactions
            ->groupBy(function ($transaction) {
                return $transaction->transaction_date->format('Y-m');
            })
            ->map(function ($monthTransactions) {
                $revenueByCategory = $monthTransactions
                    ->where('transaction_type', 'revenue')
                    ->groupBy('transaction_category')
                    ->map(function ($categoryTransactions) {
                        return $categoryTransactions->sum('amount');
                    });

                $expensesByCategory = $monthTransactions
                    ->where('transaction_type', 'expense')
                    ->groupBy('transaction_category')
                    ->map(function ($categoryTransactions) {
                        return $categoryTransactions->sum('amount');
                    });

                $totalRevenue = $revenueByCategory->sum();
                $totalExpenses = $expensesByCategory->sum();

                return [
                    'revenue_by_category' => $revenueByCategory,
                    'expenses_by_category' => $expensesByCategory,
                    'total_revenue' => $totalRevenue,
                    'total_expenses' => $totalExpenses,
                    'net_cash_flow' => $totalRevenue - $totalExpenses,
                ];
            });
    }

    private function calculateCategoryTotals(Collection $transactions): array
    {
        $revenueCategories = $transactions
            ->where('transaction_type', 'revenue')
            ->groupBy('transaction_category')
            ->map(function ($categoryTransactions) {
                return $categoryTransactions->sum('amount');
            });

        $expenseCategories = $transactions
            ->where('transaction_type', 'expense')
            ->groupBy('transaction_category')
            ->map(function ($categoryTransactions) {
                return $categoryTransactions->sum('amount');
            });

        return [
            'revenue_categories' => $revenueCategories,
            'expense_categories' => $expenseCategories,
        ];
    }
}
```

#### Project Transaction Report View

```blade
<x-filament::page>
    <x-filament::card>
        <form wire:submit.prevent="loadReportData">
            {{ $this->form }}

            <x-filament::button type="submit" class="mt-4">
                Generate Report
            </x-filament::button>
        </form>
    </x-filament::card>

    @if($reportData)
        <x-filament::card class="mt-6">
            <h2 class="text-xl font-bold mb-4">Monthly Project Transaction Summary</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Month</th>
                            <th scope="col" class="px-6 py-3">Total Revenue</th>
                            <th scope="col" class="px-6 py-3">Total Expenses</th>
                            <th scope="col" class="px-6 py-3">Net Cash Flow</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['monthly_data'] as $month => $data)
                            <tr class="bg-white border-b">
                                <td class="px-6 py-4">{{ $month }}</td>
                                <td class="px-6 py-4">{{ number_format($data['total_revenue'], 2) }}</td>
                                <td class="px-6 py-4">{{ number_format($data['total_expenses'], 2) }}</td>
                                <td class="px-6 py-4 {{ $data['net_cash_flow'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($data['net_cash_flow'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Revenue by Category</h3>
                    <div class="bg-white p-4 rounded-lg border">
                        <canvas id="revenueChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <table class="w-full text-sm">
                            @foreach($reportData['category_totals']['revenue_categories'] as $category => $amount)
                                <tr>
                                    <td class="py-1">{{ ucfirst($category) }}</td>
                                    <td class="py-1 text-right">{{ number_format($amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-2">Expenses by Category</h3>
                    <div class="bg-white p-4 rounded-lg border">
                        <canvas id="expenseChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <table class="w-full text-sm">
                            @foreach($reportData['category_totals']['expense_categories'] as $category => $amount)
                                <tr>
                                    <td class="py-1">{{ ucfirst($category) }}</td>
                                    <td class="py-1 text-right">{{ number_format($amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <x-filament::button wire:click="exportReport('csv')">
                    Export CSV
                </x-filament::button>
                <x-filament::button wire:click="exportReport('pdf')" class="ml-2">
                    Export PDF
                </x-filament::button>
            </div>
        </x-filament::card>
    @endif

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('livewire:load', function () {
                Livewire.hook('message.processed', (message, component) => {
                    if (document.getElementById('revenueChart')) {
                        renderCharts();
                    }
                });

                function renderCharts() {
                    const revenueData = @json($reportData['category_totals']['revenue_categories'] ?? []);
                    const expenseData = @json($reportData['category_totals']['expense_categories'] ?? []);

                    new Chart(document.getElementById('revenueChart'), {
                        type: 'pie',
                        data: {
                            labels: Object.keys(revenueData).map(key => key.charAt(0).toUpperCase() + key.slice(1)),
                            datasets: [{
                                data: Object.values(revenueData),
                                backgroundColor: [
                                    '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                                    '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                }
                            }
                        }
                    });

                    new Chart(document.getElementById('expenseChart'), {
                        type: 'pie',
                        data: {
                            labels: Object.keys(expenseData).map(key => key.charAt(0).toUpperCase() + key.slice(1)),
                            datasets: [{
                                data: Object.values(expenseData),
                                backgroundColor: [
                                    '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                                    '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
</x-filament::page>
```

## Data Models

### Database Schema

#### User Transactions Table

```sql
CREATE TABLE user_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    transaction_type ENUM('deposit', 'withdrawal') NOT NULL,
    transaction_date DATE NOT NULL,
    payment_method VARCHAR(50),
    notes TEXT,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE INDEX idx_user_transactions_user_id ON user_transactions(user_id);
CREATE INDEX idx_user_transactions_type ON user_transactions(transaction_type);
CREATE INDEX idx_user_transactions_date ON user_transactions(transaction_date);
```

#### Project Transactions Table Enhancement

```sql
ALTER TABLE project_transactions
ADD COLUMN transaction_category VARCHAR(50) NOT NULL AFTER amount,
ADD COLUMN transaction_type ENUM('revenue', 'expense') NOT NULL AFTER transaction_category;

CREATE INDEX idx_project_transactions_type_category ON project_transactions(transaction_type, transaction_category);
CREATE INDEX idx_project_transactions_date ON project_transactions(transaction_date);
```

### Data Relationships

```
User (1) ──── (N) UserTransaction

Project (1) ──── (N) ProjectTransaction
```

## Export Functionality

### CSV Export Implementation

```php
public function exportReport(string $format): Response
{
    if ($format === 'csv') {
        $filename = 'report_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, ['Month', 'Total Deposits', 'Total Withdrawals', 'Net Deposits']);

            // Add data
            foreach ($this->reportData['monthly_data'] as $month => $data) {
                fputcsv($file, [
                    $month,
                    $data['deposits'],
                    $data['withdrawals'],
                    $data['net_deposits'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Handle PDF export
    // ...
}
```

### PDF Export Implementation

```php
public function exportReport(string $format): Response
{
    if ($format === 'pdf') {
        $pdf = PDF::loadView('reports.export.investment-pdf', [
            'reportData' => $this->reportData,
            'generatedAt' => now(),
            'filters' => $this->form->getState(),
        ]);

        return $pdf->download('investment_report_' . now()->format('Y-m-d') . '.pdf');
    }

    // Handle CSV export
    // ...
}
```

## Testing Strategy

### Unit Testing

```php
class InvestmentReportServiceTest extends TestCase
{
    public function test_calculates_monthly_data_correctly(): void
    {
        // Test monthly data calculation logic
    }

    public function test_identifies_active_investors_correctly(): void
    {
        // Test active investor identification logic
    }
}
```

### Feature Testing

```php
class UserInvestmentReportPageTest extends TestCase
{
    public function test_displays_monthly_investment_data(): void
    {
        // Test report page displays correct data
    }

    public function test_exports_report_in_csv_format(): void
    {
        // Test CSV export functionality
    }
}
```

### Testing Approach

1. **Test-Driven Development**: Write tests before implementing features
2. **Mock External Dependencies**: Use mocks for database and external services
3. **Data Factories**: Create realistic test data using Laravel factories
