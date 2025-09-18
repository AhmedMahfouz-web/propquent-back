<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashflowResource\Pages;
use App\Models\Project;
use App\Models\ProjectTransaction;
use App\Models\UserTransaction;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Js;

class CashflowResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $modelLabel = 'Cashflow';

    protected static ?string $navigationGroup = 'Financial Reports';

    protected static ?string $title = 'Cashflow';

    protected static ?string $pluralModelLabel = 'Cashflow';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // This resource is read-only, no form needed
            ]);
    }

    public static function table(Table $table): Table
    {
        // Generate weekly columns for 3 months (12 weeks)
        $weeklyColumns = [];
        $startDate = now()->startOfWeek();

        for ($i = 0; $i < 12; $i++) {
            $weekStart = $startDate->copy()->addWeeks($i);
            $weekEnd = $weekStart->copy()->endOfWeek();

            // Determine which month this week belongs to
            $monthName = $weekStart->format('M-y');
            $weekNumber = 'W' . (($i % 4) + 1);

            $expectedCash = self::calculateExpectedCashForWeek($weekStart, $weekEnd);

            $weeklyColumns[] = Tables\Columns\TextColumn::make("week_{$i}")
                ->label($weekNumber)
                ->html()
                ->getStateUsing(function ($record) use ($weekStart, $weekEnd, $expectedCash) {
                    $transactions = $record->transactions()
                        ->where('status', 'pending')
                        ->whereBetween('due_date', [$weekStart, $weekEnd])
                        ->get();

                    $cashColor = $expectedCash >= 0 ? 'text-green-600' : 'text-red-600';
                    $html = "<div class='text-xs font-semibold {$cashColor} text-center mb-2 p-1 bg-gray-100 rounded'>";
                    $html .= number_format($expectedCash, 0);
                    $html .= "</div>";

                    if ($transactions->isEmpty()) {
                        $html .= '<div class="text-gray-400 text-xs text-center">-</div>';
                        return $html;
                    }

                    foreach ($transactions as $transaction) {
                        $color = $transaction->financial_type === 'revenue' ? 'text-green-600' : 'text-red-600';
                        $bg = $transaction->financial_type === 'revenue' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
                        $html .= "<div class='text-xs p-2 mb-1 rounded border {$bg} {$color} cursor-help' title='" . ucfirst($transaction->financial_type) . " - Due: " . Carbon::parse($transaction->due_date)->format('M j') . "'>";
                        $html .= number_format($transaction->amount, 0);
                        $html .= "</div>";
                    }

                    return $html;
                })
                ->width('80px')
                ->extraHeaderAttributes([
                    'data-month' => $monthName,
                    'class' => 'text-center border-l border-gray-300'
                ]);
        }

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->width('100px'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Project')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->width('200px'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->extraAttributes([
                        'class' => 'project-status-badge'
                    ])
                    ->width('100px'),

                ...$weeklyColumns,
            ])
            ->headerActions([
                Tables\Actions\Action::make('add_month_headers')
                    ->label('')
                    ->action(function () {})
                    ->extraAttributes([
                        'style' => 'display: none;'
                    ])
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Project Status')
                    ->options([
                        'active' => 'Active',
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                // No actions needed for read-only cashflow
            ])
            ->bulkActions([
                // No bulk actions for read-only resource
            ])
            ->defaultSort('status', 'asc')
            ->poll('60s'); // Auto-refresh every minute
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashflows::route('/'),
        ];
    }

    /**
     * Get all projects for cashflow projection with ongoing projects on top
     */
    public static function getEloquentQuery(): Builder
    {
        return Project::query()
            ->with(['transactions' => function ($query) {
                $query->where('status', 'pending')
                    ->where('due_date', '>=', now())
                    ->where('due_date', '<=', now()->addWeeks(12))
                    ->orderBy('due_date');
            }])
            ->orderByRaw("CASE WHEN status = 'active' THEN 1 WHEN status = 'pending' THEN 2 ELSE 3 END")
            ->orderBy('title');
    }

    /**
     * Get current cash balance from all completed transactions
     */
    public static function getCurrentCashBalance(): float
    {
        return Cache::remember('current_cash_balance', now()->addMinutes(5), function () {
            // Project transactions
            $projectBalance = DB::table('project_transactions')
                ->where('status', 'done')
                ->where('transaction_date', '<', now()->startOfDay())
                ->selectRaw('
                    SUM(CASE WHEN financial_type = "revenue" THEN amount ELSE 0 END) -
                    SUM(CASE WHEN financial_type = "expense" THEN amount ELSE 0 END) as balance
                ')
                ->value('balance') ?? 0;

            // User transactions
            $userBalance = DB::table('user_transactions')
                ->where('status', 'done')
                ->where('transaction_date', '<', now()->startOfDay())
                ->selectRaw('
                    SUM(CASE WHEN transaction_type = "deposit" THEN amount ELSE 0 END) -
                    SUM(CASE WHEN transaction_type = "withdraw" THEN amount ELSE 0 END) as balance
                ')
                ->value('balance') ?? 0;

            return (float) ($projectBalance + $userBalance);
        });
    }

    /**
     * Get cashflow summary for dashboard widgets
     */
    public static function getCashflowSummary(): array
    {
        return Cache::remember('cashflow_summary', now()->addMinutes(10), function () {
            $currentBalance = self::getCurrentCashBalance();

            // Get pending transactions for next 30 days
            $next30Days = now()->addDays(30);

            $pendingIn = DB::table('project_transactions')
                ->where('status', 'pending')
                ->where('financial_type', 'revenue')
                ->whereBetween('due_date', [now(), $next30Days])
                ->sum('amount') ?? 0;

            // User transactions don't use due_date - they're immediate
            // So we don't include pending user transactions in projections

            $pendingOut = DB::table('project_transactions')
                ->where('status', 'pending')
                ->where('financial_type', 'expense')
                ->whereBetween('due_date', [now(), $next30Days])
                ->sum('amount') ?? 0;

            return [
                'current_balance' => (float) $currentBalance,
                'pending_in_30_days' => (float) $pendingIn,
                'pending_out_30_days' => (float) $pendingOut,
                'projected_balance_30_days' => (float) ($currentBalance + $pendingIn - $pendingOut),
            ];
        });
    }

    /**
     * Get monthly cashflow data for chart
     */
    public static function getMonthlyCashflowData(int $months = 12, bool $startFromToday = false): array
    {
        return Cache::remember("monthly_cashflow_data_{$months}_" . ($startFromToday ? 'today' : 'historical'), now()->addMinutes(15), function () use ($months, $startFromToday) {
            if ($startFromToday) {
                $startDate = now()->startOfMonth();
                $endDate = now()->addMonths($months)->endOfMonth();
            } else {
                $startDate = now()->subMonths($months)->startOfMonth();
                $endDate = now()->endOfMonth();
            }

            // Get current balance as starting point
            $runningBalance = self::getCurrentCashBalance();

            // For future projections, include pending transactions
            if ($startFromToday) {
                // Get completed transactions for historical months (before today)
                $historicalProjectData = collect(DB::table('project_transactions')
                    ->where('status', 'done')
                    ->where('transaction_date', '<', now()->startOfMonth())
                    ->selectRaw('
                        DATE_FORMAT(transaction_date, "%Y-%m") as month,
                        SUM(CASE WHEN financial_type = "revenue" THEN amount ELSE 0 END) as revenue,
                        SUM(CASE WHEN financial_type = "expense" THEN amount ELSE 0 END) as expenses
                    ')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->toArray())
                    ->keyBy('month');

                $historicalUserData = collect(DB::table('user_transactions')
                    ->where('status', 'done')
                    ->where('transaction_date', '<', now()->startOfMonth())
                    ->selectRaw('
                        DATE_FORMAT(transaction_date, "%Y-%m") as month,
                        SUM(CASE WHEN transaction_type = "deposit" THEN amount ELSE 0 END) as deposits,
                        SUM(CASE WHEN transaction_type = "withdraw" THEN amount ELSE 0 END) as withdrawals
                    ')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->toArray())
                    ->keyBy('month');

                // Get future transactions (pending with due_date)
                $futureProjectData = collect(DB::table('project_transactions')
                    ->where('status', 'pending')
                    ->whereBetween('due_date', [$startDate, $endDate])
                    ->selectRaw('
                        DATE_FORMAT(due_date, "%Y-%m") as month,
                        SUM(CASE WHEN financial_type = "revenue" THEN amount ELSE 0 END) as revenue,
                        SUM(CASE WHEN financial_type = "expense" THEN amount ELSE 0 END) as expenses
                    ')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->toArray())
                    ->keyBy('month');

                $monthlyProjectData = $futureProjectData;
                $monthlyUserData = collect([]); // User transactions are immediate, no future projections
            } else {
                // Historical view - only completed transactions
                $monthlyProjectData = collect(DB::table('project_transactions')
                    ->where('status', 'done')
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->selectRaw('
                        DATE_FORMAT(transaction_date, "%Y-%m") as month,
                        SUM(CASE WHEN financial_type = "revenue" THEN amount ELSE 0 END) as revenue,
                        SUM(CASE WHEN financial_type = "expense" THEN amount ELSE 0 END) as expenses
                    ')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->toArray())
                    ->keyBy('month');

                $monthlyUserData = collect(DB::table('user_transactions')
                    ->where('status', 'done')
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->selectRaw('
                        DATE_FORMAT(transaction_date, "%Y-%m") as month,
                        SUM(CASE WHEN transaction_type = "deposit" THEN amount ELSE 0 END) as deposits,
                        SUM(CASE WHEN transaction_type = "withdraw" THEN amount ELSE 0 END) as withdrawals
                    ')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->toArray())
                    ->keyBy('month');

                $runningBalance = 0; // Start from 0 for historical view
            }

            // Generate all months in range
            $monthlyData = [];
            $currentMonth = $startDate->copy();

            while ($currentMonth <= $endDate) {
                $monthKey = $currentMonth->format('Y-m');

                $projectData = $monthlyProjectData->get($monthKey);
                $userData = $monthlyUserData->get($monthKey);

                $revenue = $projectData->revenue ?? 0;
                $expenses = $projectData->expenses ?? 0;
                $deposits = $userData->deposits ?? 0;
                $withdrawals = $userData->withdrawals ?? 0;

                $monthlyNet = $revenue + $deposits - $expenses - $withdrawals;
                $runningBalance += $monthlyNet;

                $monthlyData[] = [
                    'month' => $monthKey,
                    'month_label' => $currentMonth->format('M Y'),
                    'revenue' => (float) $revenue,
                    'expenses' => (float) $expenses,
                    'deposits' => (float) $deposits,
                    'withdrawals' => (float) $withdrawals,
                    'monthly_net' => (float) $monthlyNet,
                    'running_balance' => (float) $runningBalance,
                ];

                $currentMonth->addMonth();
            }

            return $monthlyData;
        });
    }

    /**
     * Calculate expected cash in hand for a specific week
     */
    public static function calculateExpectedCashForWeek($weekStart, $weekEnd): float
    {
        // Start with current balance
        $currentBalance = self::getCurrentCashBalance();

        // Add all project transactions that should be completed by the end of this week
        $weeklyProjectTransactions = DB::table('project_transactions')
            ->where('status', 'pending')
            ->where('due_date', '<=', $weekEnd)
            ->where('due_date', '>=', now())
            ->selectRaw('
                SUM(CASE WHEN financial_type = "revenue" THEN amount ELSE 0 END) as revenue,
                SUM(CASE WHEN financial_type = "expense" THEN amount ELSE 0 END) as expenses
            ')
            ->first();

        // Add all user transactions that should be completed by the end of this week
        $weeklyUserTransactions = DB::table('user_transactions')
            ->where('status', 'pending')
            ->where('transaction_date', '<=', $weekEnd)
            ->where('transaction_date', '>=', now())
            ->selectRaw('
                SUM(CASE WHEN transaction_type = "deposit" THEN amount ELSE 0 END) as deposits,
                SUM(CASE WHEN transaction_type = "withdraw" THEN amount ELSE 0 END) as withdrawals
            ')
            ->first();

        $projectRevenue = $weeklyProjectTransactions->revenue ?? 0;
        $projectExpenses = $weeklyProjectTransactions->expenses ?? 0;
        $userDeposits = $weeklyUserTransactions->deposits ?? 0;
        $userWithdrawals = $weeklyUserTransactions->withdrawals ?? 0;

        return $currentBalance + $projectRevenue - $projectExpenses + $userDeposits - $userWithdrawals;
    }
}
