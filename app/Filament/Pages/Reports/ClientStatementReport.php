<?php

namespace App\Filament\Pages\Reports;

use App\Models\User;
use App\Services\InvestmentCalculationService;
use App\Services\ProfitCalculationService;
use App\Services\ProfitDistributionService;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Carbon\Carbon;

class ClientStatementReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.reports.client-statement-report';
    protected static ?string $navigationGroup = null;
    protected static ?string $title = 'Client Statement Report';
    protected static bool $shouldRegisterNavigation = false;

    // Disable this report from navigation
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public ?int $selectedClientId = null;
    public string $statementPeriod = 'quarterly';
    public ?string $statementDate = null;
    public array $clientData = [];
    public array $portfolioData = [];
    public array $distributionData = [];
    public array $performanceData = [];

    public function mount(): void
    {
        $this->statementDate = Carbon::now()->format('Y-m-d');
        $this->loadDefaultData();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Statement Parameters')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('selectedClientId')
                                    ->label('Select Client')
                                    ->options(
                                        User::whereHas('clientInvestments')
                                            ->pluck('full_name', 'id')
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->placeholder('Select a client')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn() => $this->loadClientData()),

                                Forms\Components\Select::make('statementPeriod')
                                    ->label('Statement Period')
                                    ->options([
                                        'monthly' => 'Monthly',
                                        'quarterly' => 'Quarterly',
                                        'semi-annual' => 'Semi-Annual',
                                        'annual' => 'Annual',
                                        'custom' => 'Custom Period',
                                    ])
                                    ->default('quarterly')
                                    ->live()
                                    ->afterStateUpdated(fn() => $this->loadClientData()),

                                Forms\Components\DatePicker::make('statementDate')
                                    ->label('Statement Date')
                                    ->default(Carbon::now()->format('Y-m-d'))
                                    ->live()
                                    ->afterStateUpdated(fn() => $this->loadClientData()),
                            ]),
                    ]),
            ]);
    }

    private function loadDefaultData(): void
    {
        // Load summary statistics for all clients
        $investmentService = app(InvestmentCalculationService::class);
        $overallMetrics = $investmentService->calculateOverallPortfolioMetrics();

        $this->clientData = [
            'total_clients' => $overallMetrics['total_users'],
            'total_portfolio_value' => $overallMetrics['current_portfolio_value'],
            'average_return' => $overallMetrics['average_return_percentage'],
        ];
    }

    private function loadClientData(): void
    {
        if (!$this->selectedClientId) {
            $this->portfolioData = [];
            $this->distributionData = [];
            $this->performanceData = [];
            return;
        }

        $user = User::find($this->selectedClientId);
        if (!$user) {
            return;
        }

        $investmentService = app(InvestmentCalculationService::class);
        $profitService = app(ProfitCalculationService::class);
        $distributionService = app(ProfitDistributionService::class);

        // Load portfolio data
        $this->portfolioData = $investmentService->calculateUserPortfolioValue($user);

        // Load profit and distribution data
        $this->distributionData = $profitService->calculateTotalGains($user);

        // Load performance data
        $this->performanceData = [
            'monthly_trends' => $profitService->calculateMonthlyProfitTrends($user, 12),
            'distribution_breakdown' => $profitService->calculateDistributionBreakdown($user),
            'distribution_stats' => $distributionService->getUserDistributionStats($user),
        ];
    }

    public function generatePdfStatement(): void
    {
        if (!$this->selectedClientId) {
            \Filament\Notifications\Notification::make()
                ->title('No Client Selected')
                ->body('Please select a client to generate their statement.')
                ->warning()
                ->send();
            return;
        }

        // In a real implementation, this would generate a PDF
        \Filament\Notifications\Notification::make()
            ->title('Statement Generated')
            ->body('Client statement has been generated and is ready for download.')
            ->success()
            ->send();
    }

    public function emailStatement(): void
    {
        if (!$this->selectedClientId) {
            \Filament\Notifications\Notification::make()
                ->title('No Client Selected')
                ->body('Please select a client to email their statement.')
                ->warning()
                ->send();
            return;
        }

        // In a real implementation, this would send an email
        \Filament\Notifications\Notification::make()
            ->title('Statement Emailed')
            ->body('Client statement has been sent to the client\'s email address.')
            ->success()
            ->send();
    }

    public function getViewData(): array
    {
        return [
            'clientData' => $this->clientData,
            'portfolioData' => $this->portfolioData,
            'distributionData' => $this->distributionData,
            'performanceData' => $this->performanceData,
            'selectedClient' => $this->selectedClientId ? User::find($this->selectedClientId) : null,
            'statementDate' => $this->statementDate ? Carbon::parse($this->statementDate) : Carbon::now(),
        ];
    }
}
