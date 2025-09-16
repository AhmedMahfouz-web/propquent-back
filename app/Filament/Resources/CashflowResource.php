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

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Revenue' => 'success',
                        'Expense' => 'danger',
                        'Deposit' => 'info',
                        'Withdrawal' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('cash_in')
                    ->label('Cash In')
                    ->money('USD')
                    ->color('success')
                    ->getStateUsing(function ($record) {
                        return $record->cash_in > 0 ? $record->cash_in : null;
                    }),

                Tables\Columns\TextColumn::make('cash_out')
                    ->label('Cash Out')
                    ->money('USD')
                    ->color('danger')
                    ->getStateUsing(function ($record) {
                        return $record->cash_out > 0 ? $record->cash_out : null;
                    }),

                Tables\Columns\TextColumn::make('running_balance')
                    ->label('Running Balance')
                    ->money('USD')
                    ->color(fn($record) => $record->running_balance >= 0 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Completed' => 'success',
                        'Pending' => 'warning',
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
                            ->when($data['from'], fn($q) => $q->where('date', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->where('date', '<=', $data['until']));
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
        // Get current cash balance from completed transactions
        $currentBalance = self::getCurrentCashBalance();

        // Create a union query for both project and user transactions
        $projectTransactions = DB::table('project_transactions as pt')
            ->leftJoin('projects as p', 'pt.project_key', '=', 'p.key')
            ->select([
                DB::raw('COALESCE(pt.due_date, pt.transaction_date) as date'),
                DB::raw('CONCAT("Project: ", COALESCE(p.title, pt.project_key), " - ", pt.financial_type) as description'),
                DB::raw('CASE
                    WHEN pt.financial_type = "revenue" THEN "Revenue"
                    ELSE "Expense"
                END as type'),
                DB::raw('CASE
                    WHEN pt.financial_type = "revenue" THEN pt.amount
                    ELSE 0
                END as cash_in'),
                DB::raw('CASE
                    WHEN pt.financial_type = "expense" THEN pt.amount
                    ELSE 0
                END as cash_out'),
                DB::raw('CASE
                    WHEN pt.status = "done" THEN "Completed"
                    ELSE "Pending"
                END as status'),
                'pt.id',
                DB::raw('"project" as source_table')
            ])
            ->where('pt.due_date', '>=', now()->startOfDay())
            ->orWhere(function ($q) {
                $q->where('pt.transaction_date', '>=', now()->startOfDay())
                    ->where('pt.status', 'done');
            });

        $userTransactions = DB::table('user_transactions as ut')
            ->leftJoin('users as u', 'ut.user_id', '=', 'u.id')
            ->select([
                DB::raw('COALESCE(ut.due_date, ut.transaction_date) as date'),
                DB::raw('CONCAT("User: ", COALESCE(u.full_name, u.id), " - ", ut.transaction_type) as description'),
                DB::raw('CASE
                    WHEN ut.transaction_type = "deposit" THEN "Deposit"
                    ELSE "Withdrawal"
                END as type'),
                DB::raw('CASE
                    WHEN ut.transaction_type = "deposit" THEN ut.amount
                    ELSE 0
                END as cash_in'),
                DB::raw('CASE
                    WHEN ut.transaction_type = "withdraw" THEN ut.amount
                    ELSE 0
                END as cash_out'),
                DB::raw('CASE
                    WHEN ut.status = "done" THEN "Completed"
                    ELSE "Pending"
                END as status'),
                'ut.id',
                DB::raw('"user" as source_table')
            ])
            ->where('ut.due_date', '>=', now()->startOfDay())
            ->orWhere(function ($q) {
                $q->where('ut.transaction_date', '>=', now()->startOfDay())
                    ->where('ut.status', 'done');
            });

        // Union both queries and add running balance calculation
        $unionQuery = $projectTransactions->unionAll($userTransactions);

        // Create a temporary table-like structure with running balance
        return DB::query()
            ->fromSub($unionQuery, 'combined_transactions')
            ->selectRaw('
                *,
                @running_balance := @running_balance + cash_in - cash_out as running_balance
            ')
            ->crossJoin(DB::raw("(SELECT @running_balance := {$currentBalance}) as init"))
            ->orderBy('date')
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

            $pendingIn += DB::table('user_transactions')
                ->where('status', 'pending')
                ->where('transaction_type', 'deposit')
                ->whereBetween('due_date', [now(), $next30Days])
                ->sum('amount') ?? 0;

            $pendingOut = DB::table('project_transactions')
                ->where('status', 'pending')
                ->where('financial_type', 'expense')
                ->whereBetween('due_date', [now(), $next30Days])
                ->sum('amount') ?? 0;

            $pendingOut += DB::table('user_transactions')
                ->where('status', 'pending')
                ->where('transaction_type', 'withdraw')
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
}
