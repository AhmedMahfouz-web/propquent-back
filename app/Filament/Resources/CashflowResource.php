<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashflowResource\Pages;
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

class CashflowResource extends Resource
{
    protected static ?string $model = ProjectTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $modelLabel = 'Cashflow';

    protected static ?string $navigationGroup = 'Financial Reports';

    protected static ?string $title = 'Cashflow Projection';

    protected static ?string $pluralModelLabel = 'Cashflow Projection';

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
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Description')
                    ->searchable()
                    ->limit(50)
                    ->getStateUsing(function ($record) {
                        return "Project: " . ($record->project->title ?? $record->project_key) . " - " . $record->financial_type;
                    }),

                Tables\Columns\TextColumn::make('financial_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->color(fn(string $state): string => match ($state) {
                        'revenue' => 'success',
                        'expense' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Cash In')
                    ->money('USD')
                    ->color('success')
                    ->getStateUsing(function ($record) {
                        return $record->financial_type === 'revenue' ? $record->amount : null;
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Cash Out')
                    ->money('USD')
                    ->color('danger')
                    ->getStateUsing(function ($record) {
                        return $record->financial_type === 'expense' ? $record->amount : null;
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => $state === 'done' ? 'Completed' : 'Pending')
                    ->color(fn(string $state): string => match ($state) {
                        'done' => 'success',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date')
                            ->default(now()),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date')
                            ->default(now()->addMonths(6)),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereRaw('COALESCE(actual_date, due_date, transaction_date) >= ?', [$data['from']]))
                            ->when($data['until'], fn($q) => $q->whereRaw('COALESCE(actual_date, due_date, transaction_date) <= ?', [$data['until']]));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from']) {
                            $indicators['from'] = 'From: ' . Carbon::parse($data['from'])->toFormattedDateString();
                        }
                        if ($data['until']) {
                            $indicators['until'] = 'Until: ' . Carbon::parse($data['until'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'Revenue' => 'Revenue',
                        'Expense' => 'Expense',
                        'Deposit' => 'Deposit',
                        'Withdrawal' => 'Withdrawal',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Completed' => 'Completed',
                        'Pending' => 'Pending',
                    ]),
            ])
            ->actions([
                // No actions needed for read-only cashflow
            ])
            ->bulkActions([
                // No bulk actions for read-only resource
            ])
            ->defaultSort('date', 'asc')
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
     * Get cashflow data combining project and user transactions
     */
    public static function getEloquentQuery(): Builder
    {
        return ProjectTransaction::query()
            ->with(['project'])
            ->selectRaw('*, COALESCE(actual_date, due_date, transaction_date) as date')
            ->where(function ($query) {
                $query->where('due_date', '>=', now()->startOfDay())
                    ->orWhere(function ($q) {
                        $q->where('transaction_date', '>=', now()->startOfDay())
                            ->where('status', 'done');
                    });
            })
            ->orderByRaw('COALESCE(actual_date, due_date, transaction_date)')
            ->orderBy('id');
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
    public static function getMonthlyCashflowData(int $months = 12): array
    {
        return Cache::remember("monthly_cashflow_data_{$months}", now()->addMinutes(15), function () use ($months) {
            $startDate = now()->subMonths($months)->startOfMonth();
            $endDate = now()->endOfMonth();

            // Get monthly project transactions
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

            // Get monthly user transactions
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

            // Generate all months in range
            $monthlyData = [];
            $currentMonth = $startDate->copy();
            $runningBalance = 0;

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
}
