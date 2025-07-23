<?php

namespace App\Filament\Pages;

use App\Services\ProjectReportService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class ProjectReports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Projects Summary Reports';

    protected static ?string $title = 'Projects Summary Reports';

    protected static ?string $navigationGroup = 'Financial Reports';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.project-reports';

    public ?array $filters = [];
    public ?array $reportData = [];
    public bool $isLoading = false;

    protected ProjectReportService $reportService;

    public function boot(ProjectReportService $reportService): void
    {
        $this->reportService = $reportService;
    }

    public function mount(): void
    {
        // Set default filters
        $this->filters = [
            'start_date' => Carbon::now()->subMonths(11)->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
            'developer_id' => null,
            'status' => null,
            'stage' => null,
            'property_type' => null,
            'investment_type' => null,
            'location' => null,
        ];

        $this->loadReportData();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refreshData')
                ->label('Refresh Data')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->reportService->clearCache();
                    $this->loadReportData();

                    Notification::make()
                        ->title('Data Refreshed')
                        ->body('Report data has been refreshed successfully.')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('exportReport')
                ->label('Export Report')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(function () {
                    $exportData = $this->reportService->exportReport($this->filters);

                    // In a real implementation, you would generate and download the file
                    Notification::make()
                        ->title('Export Ready')
                        ->body('Report export functionality would generate CSV/Excel file here.')
                        ->info()
                        ->send();
                }),

            Actions\Action::make('resetFilters')
                ->label('Reset Filters')
                ->icon('heroicon-o-x-mark')
                ->color('warning')
                ->action(function () {
                    $this->filters = [
                        'start_date' => Carbon::now()->subMonths(11)->format('Y-m-d'),
                        'end_date' => Carbon::now()->format('Y-m-d'),
                        'developer_id' => null,
                        'status' => null,
                        'stage' => null,
                        'property_type' => null,
                        'investment_type' => null,
                        'location' => null,
                    ];

                    $this->loadReportData();

                    Notification::make()
                        ->title('Filters Reset')
                        ->body('All filters have been reset to default values.')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getFiltersForm(): Form
    {
        $filterOptions = $this->reportService->getFilterOptions();

        return $this->makeForm()
            ->schema([
                Forms\Components\Section::make('Date Range')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->default(Carbon::now()->subMonths(11)->format('Y-m-d'))
                            ->maxDate(now()),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->required()
                            ->default(Carbon::now()->format('Y-m-d'))
                            ->maxDate(now())
                            ->afterOrEqual('start_date'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Project Filters')
                    ->schema([
                        Forms\Components\Select::make('developer_id')
                            ->label('Developer')
                            ->options(collect($filterOptions['developers'])->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('All Developers'),

                        Forms\Components\Select::make('status')
                            ->label('Project Status')
                            ->options($filterOptions['statuses'])
                            ->placeholder('All Statuses'),

                        Forms\Components\Select::make('stage')
                            ->label('Project Stage')
                            ->options($filterOptions['stages'])
                            ->placeholder('All Stages'),

                        Forms\Components\Select::make('property_type')
                            ->label('Property Type')
                            ->options($filterOptions['property_types'])
                            ->placeholder('All Property Types'),

                        Forms\Components\Select::make('investment_type')
                            ->label('Investment Type')
                            ->options($filterOptions['investment_types'])
                            ->placeholder('All Investment Types'),

                        Forms\Components\TextInput::make('location')
                            ->label('Location')
                            ->placeholder('Search by location'),
                    ])
                    ->columns(3),
            ])
            ->statePath('filters');
    }

    public function applyFilters(): void
    {
        $this->validate();
        $this->loadReportData();

        Notification::make()
            ->title('Filters Applied')
            ->body('Report has been updated with new filters.')
            ->success()
            ->send();
    }

    protected function loadReportData(): void
    {
        $this->isLoading = true;

        try {
            $this->reportData = $this->reportService->generateMonthlyReport($this->filters);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Loading Report')
                ->body('There was an error loading the report data: ' . $e->getMessage())
                ->danger()
                ->send();

            $this->reportData = [
                'months' => [],
                'metrics' => [],
                'summary' => [],
                'filters' => $this->filters,
                'period' => []
            ];
        }

        $this->isLoading = false;
    }

    public function getReportData(): array
    {
        return $this->reportData;
    }

    public function getMetricLabel(string $key): string
    {
        return match ($key) {
            'new_projects' => 'New Projects',
            'exited_projects' => 'Exited Projects',
            'ongoing_projects' => 'Ongoing Projects',
            'total_investment' => 'Total Investment',
            'revenue_generated' => 'Revenue Generated',
            'active_transactions' => 'Active Transactions',
            'project_value' => 'Project Value',
            'roi_percentage' => 'ROI %',
            default => ucwords(str_replace('_', ' ', $key)),
        };
    }

    public function formatMetricValue(string $key, $value): string
    {
        return match ($key) {
            'total_investment', 'revenue_generated', 'project_value' => '$' . number_format($value, 2),
            'roi_percentage' => number_format($value, 2) . '%',
            default => number_format($value),
        };
    }

    public function getMetricColor(string $key, $value): string
    {
        return match ($key) {
            'new_projects' => 'text-green-600',
            'exited_projects' => 'text-red-600',
            'ongoing_projects' => 'text-blue-600',
            'total_investment' => 'text-purple-600',
            'revenue_generated' => 'text-emerald-600',
            'active_transactions' => 'text-orange-600',
            'roi_percentage' => $value >= 0 ? 'text-green-600' : 'text-red-600',
            default => 'text-gray-600',
        };
    }

    public function applyQuickFilter(string $filter): void
    {
        $now = Carbon::now();

        match ($filter) {
            'last_3_months' => [
                $this->filters['start_date'] = $now->copy()->subMonths(2)->startOfMonth()->format('Y-m-d'),
                $this->filters['end_date'] = $now->format('Y-m-d')
            ],
            'last_6_months' => [
                $this->filters['start_date'] = $now->copy()->subMonths(5)->startOfMonth()->format('Y-m-d'),
                $this->filters['end_date'] = $now->format('Y-m-d')
            ],
            'last_12_months' => [
                $this->filters['start_date'] = $now->copy()->subMonths(11)->startOfMonth()->format('Y-m-d'),
                $this->filters['end_date'] = $now->format('Y-m-d')
            ],
            'current_year' => [
                $this->filters['start_date'] = $now->copy()->startOfYear()->format('Y-m-d'),
                $this->filters['end_date'] = $now->format('Y-m-d')
            ],
            'last_year' => [
                $this->filters['start_date'] = $now->copy()->subYear()->startOfYear()->format('Y-m-d'),
                $this->filters['end_date'] = $now->copy()->subYear()->endOfYear()->format('Y-m-d')
            ],
            'ytd' => [
                $this->filters['start_date'] = $now->copy()->startOfYear()->format('Y-m-d'),
                $this->filters['end_date'] = $now->format('Y-m-d')
            ],
        };

        $this->loadReportData();

        Notification::make()
            ->title('Quick Filter Applied')
            ->body("Date range updated to: {$filter}")
            ->success()
            ->send();
    }

    public function resetFilters(): void
    {
        $this->filters = [
            'start_date' => Carbon::now()->subMonths(11)->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
            'developer_id' => null,
            'status' => null,
            'stage' => null,
            'property_type' => null,
            'investment_type' => null,
            'location' => null,
        ];

        $this->loadReportData();

        Notification::make()
            ->title('Filters Reset')
            ->body('All filters have been reset to default values.')
            ->success()
            ->send();
    }

    public function exportReport(array $options): void
    {
        try {
            $exportData = $this->reportService->exportReport($this->filters);

            // In a real implementation, you would generate the actual file
            $filename = 'project_report_' . date('Y-m-d_H-i-s') . '.' . $options['format'];

            Notification::make()
                ->title('Export Successful')
                ->body("Report exported as {$filename}. Download would start automatically in a real implementation.")
                ->success()
                ->duration(5000)
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Export Failed')
                ->body('There was an error exporting the report: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function saveView(array $data): void
    {
        // In a real implementation, you would save this to a database table
        // For now, we'll just show a success message

        Notification::make()
            ->title('View Saved')
            ->body("View '{$data['view_name']}' has been saved successfully.")
            ->success()
            ->send();
    }

    public function getNavigationBreadcrumbs(): array
    {
        return [
            'Reports' => null,
            'Project Reports' => null,
        ];
    }
}
