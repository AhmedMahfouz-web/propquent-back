<?php

namespace App\Filament\Pages\Reports;

use App\Models\Project;
use App\Models\Developer;
use App\Services\InvestmentCalculationService;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class PropertyPerformanceReport extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static string $view = 'filament.pages.reports.property-performance-report';
    protected static ?string $navigationGroup = 'Financial Reports';
    protected static ?string $title = 'Property Performance Report';
    protected static ?int $navigationSort = 4;

    // Disable this report from navigation
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public ?int $selectedDeveloperId = null;
    public string $propertyTypeFilter = 'all';
    public string $statusFilter = 'all';
    public array $performanceData = [];

    public function mount(): void
    {
        $this->loadPerformanceData();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Report Filters')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('selectedDeveloperId')
                                    ->label('Developer')
                                    ->options(
                                        Developer::has('projects')
                                            ->pluck('name', 'id')
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->placeholder('All Developers')
                                    ->live()
                                    ->afterStateUpdated(fn() => $this->resetTable()),

                                Forms\Components\Select::make('propertyTypeFilter')
                                    ->label('Property Type')
                                    ->options([
                                        'all' => 'All Types',
                                        'residential' => 'Residential',
                                        'commercial' => 'Commercial',
                                        'mixed' => 'Mixed Use',
                                        'industrial' => 'Industrial',
                                    ])
                                    ->default('all')
                                    ->live()
                                    ->afterStateUpdated(fn() => $this->resetTable()),

                                Forms\Components\Select::make('statusFilter')
                                    ->label('Status')
                                    ->options([
                                        'all' => 'All Status',
                                        'available' => 'Available',
                                        'sold' => 'Sold',
                                        'reserved' => 'Reserved',
                                        'completed' => 'Completed',
                                    ])
                                    ->default('all')
                                    ->live()
                                    ->afterStateUpdated(fn() => $this->resetTable()),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('project_key')
                    ->label('Property ID')
                    ->getStateUsing(fn(Project $record) => $record->getDisplayIdentifier())
                    ->searchable(['project_key', 'id']),

                Tables\Columns\TextColumn::make('title')
                    ->label('Property Name')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('developer.name')
                    ->label('Developer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'residential' => 'success',
                        'commercial' => 'info',
                        'mixed' => 'warning',
                        'industrial' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_invested')
                    ->label('Total Investment')
                    ->money('USD')
                    ->getStateUsing(fn(Project $record) => $record->getTotalInvestmentAmount()),

                Tables\Columns\TextColumn::make('investor_count')
                    ->label('Investors')
                    ->numeric()
                    ->getStateUsing(fn(Project $record) => $record->getActiveInvestorsCount()),

                Tables\Columns\TextColumn::make('current_value')
                    ->label('Current Value')
                    ->money('USD')
                    ->getStateUsing(fn(Project $record) => $this->calculateCurrentValue($record)),

                Tables\Columns\TextColumn::make('return_amount')
                    ->label('Total Return')
                    ->money('USD')
                    ->color(fn(float $state): string => $state >= 0 ? 'success' : 'danger')
                    ->getStateUsing(fn(Project $record) => $this->calculateCurrentValue($record) - $record->getTotalInvestmentAmount()),

                Tables\Columns\TextColumn::make('return_percentage')
                    ->label('Return %')
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%')
                    ->color(fn(float $state): string => $state >= 0 ? 'success' : 'danger')
                    ->getStateUsing(fn(Project $record) => $this->calculateReturnPercentage($record)),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'available',
                        'info' => 'sold',
                        'danger' => 'cancelled',
                        'gray' => 'reserved',
                    ]),

                Tables\Columns\TextColumn::make('stage')
                    ->label('Stage')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'planning' => 'gray',
                        'construction' => 'warning',
                        'completed' => 'success',
                        'delivered' => 'info',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\Filter::make('high_performance')
                    ->label('High Performance (>10% Return)')
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('clientInvestments', function ($q) {
                            $q->where('status', 'active');
                        });
                    }),

                Tables\Filters\Filter::make('large_investments')
                    ->label('Large Investments (>$100K)')
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('clientInvestments', function ($q) {
                            $q->where('status', 'active')
                                ->havingRaw('SUM(investment_amount) > 100000')
                                ->groupBy('project_id');
                        });
                    }),

                Tables\Filters\Filter::make('multiple_investors')
                    ->label('Multiple Investors (>5)')
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('clientInvestments', function ($q) {
                            $q->where('status', 'active')
                                ->havingRaw('COUNT(DISTINCT user_id) > 5')
                                ->groupBy('project_id');
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('View Details')
                    ->icon('heroicon-m-eye')
                    ->url(fn(Project $record): string => route('filament.admin.resources.projects.view', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('performance_analysis')
                    ->label('Performance Analysis')
                    ->icon('heroicon-m-chart-bar')
                    ->action(function (Project $record) {
                        $investmentService = app(InvestmentCalculationService::class);
                        $performance = $investmentService->calculateProjectPerformance($record);

                        $details = "Property Performance Analysis:\n\n" .
                            "Property: {$performance['project_name']}\n" .
                            "ID: {$performance['project_key']}\n" .
                            "Total Invested: $" . number_format($performance['total_invested'], 2) . "\n" .
                            "Current Value: $" . number_format($performance['current_value'], 2) . "\n" .
                            "Total Return: $" . number_format($performance['total_return'], 2) . "\n" .
                            "Return %: " . number_format($performance['return_percentage'], 2) . "%\n" .
                            "Investors: {$performance['unique_investors']}\n" .
                            "Investments: {$performance['investment_count']}";

                        \Filament\Notifications\Notification::make()
                            ->title('Property Performance Analysis')
                            ->body($details)
                            ->info()
                            ->send();
                    }),
            ])
            ->defaultSort('title', 'asc')
            ->paginated([25, 50, 100]);
    }

    protected function getTableQuery(): Builder
    {
        $query = Project::query()
            ->whereHas('clientInvestments', function (Builder $q) {
                $q->where('status', 'active');
            })
            ->with(['clientInvestments' => function ($q) {
                $q->where('status', 'active');
            }, 'developer']);

        if ($this->selectedDeveloperId) {
            $query->where('developer_id', $this->selectedDeveloperId);
        }

        if ($this->propertyTypeFilter !== 'all') {
            $query->where('type', $this->propertyTypeFilter);
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $query;
    }

    private function calculateCurrentValue(Project $project): float
    {
        $investments = $project->clientInvestments()->where('status', 'active')->get();

        return $investments->sum(function ($investment) {
            return $investment->getCurrentValue();
        });
    }

    private function calculateReturnPercentage(Project $project): float
    {
        $totalInvested = $project->getTotalInvestmentAmount();
        $currentValue = $this->calculateCurrentValue($project);

        if ($totalInvested == 0) {
            return 0;
        }

        return (($currentValue - $totalInvested) / $totalInvested) * 100;
    }

    private function loadPerformanceData(): void
    {
        $investmentService = app(InvestmentCalculationService::class);

        $this->performanceData = [
            'total_properties' => Project::whereHas('clientInvestments')->count(),
            'top_performers' => $investmentService->getTopPerformingProjects(5),
            'property_types' => $this->getPropertyTypeBreakdown(),
            'developer_performance' => $this->getDeveloperPerformance(),
        ];
    }

    private function getPropertyTypeBreakdown(): array
    {
        return Project::whereHas('clientInvestments', function (Builder $query) {
            $query->where('status', 'active');
        })->get()->groupBy('type')->map(function ($projects, $type) {
            $totalInvested = $projects->sum(fn($p) => $p->getTotalInvestmentAmount());
            $currentValue = $projects->sum(fn($p) => $this->calculateCurrentValue($p));
            $returnPercentage = $totalInvested > 0 ? (($currentValue - $totalInvested) / $totalInvested) * 100 : 0;

            return [
                'type' => $type,
                'count' => $projects->count(),
                'total_invested' => $totalInvested,
                'current_value' => $currentValue,
                'return_percentage' => $returnPercentage,
            ];
        })->values()->toArray();
    }

    private function getDeveloperPerformance(): array
    {
        return Developer::whereHas('projects.clientInvestments', function (Builder $query) {
            $query->where('status', 'active');
        })->with(['projects.clientInvestments' => function ($query) {
            $query->where('status', 'active');
        }])->get()->map(function ($developer) {
            $projects = $developer->projects->filter(fn($p) => $p->clientInvestments->count() > 0);
            $totalInvested = $projects->sum(fn($p) => $p->getTotalInvestmentAmount());
            $currentValue = $projects->sum(fn($p) => $this->calculateCurrentValue($p));
            $returnPercentage = $totalInvested > 0 ? (($currentValue - $totalInvested) / $totalInvested) * 100 : 0;

            return [
                'name' => $developer->name,
                'project_count' => $projects->count(),
                'total_invested' => $totalInvested,
                'return_percentage' => $returnPercentage,
            ];
        })->sortByDesc('return_percentage')->take(5)->values()->toArray();
    }

    public function getViewData(): array
    {
        return [
            'performanceData' => $this->performanceData,
        ];
    }
}
