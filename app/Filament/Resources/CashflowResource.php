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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CashflowResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $modelLabel = 'Cashflow';

    protected static ?string $navigationGroup = 'Financial Reports';

    protected static ?string $title = 'Cashflow Analysis';

    protected static ?string $pluralModelLabel = 'Cashflow Analysis';

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
                Tables\Columns\TextColumn::make('key')
                    ->label('Project Key')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Project Title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('developer.name')
                    ->label('Developer')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'info',
                        'exited' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Total Revenue')
                    ->money('USD')
                    ->sortable()
                    ->color('success'),

                Tables\Columns\TextColumn::make('total_expenses')
                    ->label('Total Expenses')
                    ->money('USD')
                    ->sortable()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('net_cashflow')
                    ->label('Net Cashflow')
                    ->money('USD')
                    ->sortable()
                    ->color(fn($record) => $record->net_cashflow >= 0 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('unpaid_installments')
                    ->label('Unpaid Installments')
                    ->money('USD')
                    ->sortable()
                    ->color('warning'),

                // Month 3 Ago - Weekly Breakdown
                Tables\Columns\TextColumn::make('month_3_ago_w1')
                    ->label(now()->subMonths(3)->format('M Y') . ' W1')
                    ->money('USD')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('month_3_ago_w2')
                    ->label(now()->subMonths(3)->format('M Y') . ' W2')
                    ->money('USD')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('month_3_ago_w3')
                    ->label(now()->subMonths(3)->format('M Y') . ' W3')
                    ->money('USD')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('month_3_ago_w4')
                    ->label(now()->subMonths(3)->format('M Y') . ' W4')
                    ->money('USD')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Month 2 Ago - Weekly Breakdown
                Tables\Columns\TextColumn::make('month_2_ago_w1')
                    ->label(now()->subMonths(2)->format('M Y') . ' W1')
                    ->money('USD')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('month_2_ago_w2')
                    ->label(now()->subMonths(2)->format('M Y') . ' W2')
                    ->money('USD')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('month_2_ago_w3')
                    ->label(now()->subMonths(2)->format('M Y') . ' W3')
                    ->money('USD')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('month_2_ago_w4')
                    ->label(now()->subMonths(2)->format('M Y') . ' W4')
                    ->money('USD')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Month 1 Ago - Weekly Breakdown
                Tables\Columns\TextColumn::make('month_1_ago_w1')
                    ->label(now()->subMonths(1)->format('M Y') . ' W1')
                    ->money('USD')
                    ->sortable()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('month_1_ago_w2')
                    ->label(now()->subMonths(1)->format('M Y') . ' W2')
                    ->money('USD')
                    ->sortable()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('month_1_ago_w3')
                    ->label(now()->subMonths(1)->format('M Y') . ' W3')
                    ->money('USD')
                    ->sortable()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('month_1_ago_w4')
                    ->label(now()->subMonths(1)->format('M Y') . ' W4')
                    ->money('USD')
                    ->sortable()
                    ->color('info')
                    ->toggleable(),

                // Current Month - Weekly Breakdown (Always Visible)
                Tables\Columns\TextColumn::make('current_month_w1')
                    ->label(now()->format('M Y') . ' W1 (Current)')
                    ->money('USD')
                    ->sortable()
                    ->color('success'),

                Tables\Columns\TextColumn::make('current_month_w2')
                    ->label(now()->format('M Y') . ' W2 (Current)')
                    ->money('USD')
                    ->sortable()
                    ->color('success'),

                Tables\Columns\TextColumn::make('current_month_w3')
                    ->label(now()->format('M Y') . ' W3 (Current)')
                    ->money('USD')
                    ->sortable()
                    ->color('success'),

                Tables\Columns\TextColumn::make('current_month_w4')
                    ->label(now()->format('M Y') . ' W4 (Current)')
                    ->money('USD')
                    ->sortable()
                    ->color('success'),

                // Month 1 Ahead - Weekly Breakdown
                Tables\Columns\TextColumn::make('month_1_ahead_w1')
                    ->label(now()->addMonths(1)->format('M Y') . ' W1')
                    ->money('USD')
                    ->sortable()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('month_1_ahead_w2')
                    ->label(now()->addMonths(1)->format('M Y') . ' W2')
                    ->money('USD')
                    ->sortable()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('month_1_ahead_w3')
                    ->label(now()->addMonths(1)->format('M Y') . ' W3')
                    ->money('USD')
                    ->sortable()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('month_1_ahead_w4')
                    ->label(now()->addMonths(1)->format('M Y') . ' W4')
                    ->money('USD')
                    ->sortable()
                    ->color('warning'),

                // Month 2 Ahead - Weekly Breakdown
                Tables\Columns\TextColumn::make('month_2_ahead_w1')
                    ->label(now()->addMonths(2)->format('M Y') . ' W1')
                    ->money('USD')
                    ->sortable()
                    ->color('danger')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('month_2_ahead_w2')
                    ->label(now()->addMonths(2)->format('M Y') . ' W2')
                    ->money('USD')
                    ->sortable()
                    ->color('danger')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('month_2_ahead_w3')
                    ->label(now()->addMonths(2)->format('M Y') . ' W3')
                    ->money('USD')
                    ->sortable()
                    ->color('danger')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('month_2_ahead_w4')
                    ->label(now()->addMonths(2)->format('M Y') . ' W4')
                    ->money('USD')
                    ->sortable()
                    ->color('danger')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('next_installment_date')
                    ->label('Next Installment')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(Project::getAvailableStatuses()),

                Tables\Filters\SelectFilter::make('developer')
                    ->relationship('developer', 'name'),

                Tables\Filters\Filter::make('month_range')
                    ->form([
                        Forms\Components\DatePicker::make('start_month')
                            ->label('Start Month')
                            ->displayFormat('Y-m')
                            ->format('Y-m-01')
                            ->default(function () {
                                $minDate = DB::table('project_transactions')
                                    ->selectRaw('MIN(COALESCE(transaction_date, due_date)) as min_date')
                                    ->first()->min_date;
                                return $minDate ? Carbon::parse($minDate)->startOfMonth()->format('Y-m-01') : Carbon::now()->subMonths(6)->startOfMonth()->format('Y-m-01');
                            }),
                        Forms\Components\DatePicker::make('end_month')
                            ->label('End Month')
                            ->displayFormat('Y-m')
                            ->format('Y-m-01')
                            ->default(function () {
                                $maxDate = DB::table('project_transactions')
                                    ->selectRaw('MAX(COALESCE(due_date, transaction_date)) as max_date')
                                    ->first()->max_date;
                                return $maxDate ? Carbon::parse($maxDate)->startOfMonth()->format('Y-m-01') : Carbon::now()->addMonths(6)->startOfMonth()->format('Y-m-01');
                            }),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['start_month'] && !$data['end_month']) {
                            return $query;
                        }

                        return $query->whereHas('transactions', function ($q) use ($data) {
                            if ($data['start_month'] && $data['end_month']) {
                                $startDate = Carbon::parse($data['start_month'])->startOfMonth();
                                $endDate = Carbon::parse($data['end_month'])->endOfMonth();

                                $q->where(function ($subQuery) use ($startDate, $endDate) {
                                    $subQuery->whereBetween('transaction_date', [$startDate, $endDate])
                                        ->orWhereBetween('due_date', [$startDate, $endDate]);
                                });
                            } elseif ($data['start_month']) {
                                $startDate = Carbon::parse($data['start_month'])->startOfMonth();

                                $q->where(function ($subQuery) use ($startDate) {
                                    $subQuery->where('transaction_date', '>=', $startDate)
                                        ->orWhere('due_date', '>=', $startDate);
                                });
                            } elseif ($data['end_month']) {
                                $endDate = Carbon::parse($data['end_month'])->endOfMonth();

                                $q->where(function ($subQuery) use ($endDate) {
                                    $subQuery->where('transaction_date', '<=', $endDate)
                                        ->orWhere('due_date', '<=', $endDate);
                                });
                            }
                        });
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['start_month']) {
                            $indicators['start_month'] = 'From: ' . Carbon::parse($data['start_month'])->format('M Y');
                        }
                        if ($data['end_month']) {
                            $indicators['end_month'] = 'Until: ' . Carbon::parse($data['end_month'])->format('M Y');
                        }
                        return $indicators;
                    }),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['from'] && !$data['until']) {
                            return $query;
                        }

                        return $query->whereHas('transactions', function ($q) use ($data) {
                            if ($data['from'] && $data['until']) {
                                $q->where(function ($subQuery) use ($data) {
                                    $subQuery->whereBetween('transaction_date', [$data['from'], $data['until']])
                                        ->orWhereBetween('due_date', [$data['from'], $data['until']]);
                                });
                            } elseif ($data['from']) {
                                $q->where(function ($subQuery) use ($data) {
                                    $subQuery->where('transaction_date', '>=', $data['from'])
                                        ->orWhere('due_date', '>=', $data['from']);
                                });
                            } elseif ($data['until']) {
                                $q->where(function ($subQuery) use ($data) {
                                    $subQuery->where('transaction_date', '<=', $data['until'])
                                        ->orWhere('due_date', '<=', $data['until']);
                                });
                            }
                        });
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // No bulk actions for read-only resource
                ]),
            ])
            ->defaultSort('net_cashflow', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashflows::route('/'),
            'view' => Pages\ViewCashflow::route('/{record}'),
        ];
    }

    /**
     * Get optimized cashflow data with performance considerations
     */
    public static function getEloquentQuery(): Builder
    {
        // Don't cache the query builder itself, just the data
        return parent::getEloquentQuery()
            ->with(['developer', 'transactions' => function ($query) {
                $query->where('status', 'done')
                    ->select(['id', 'project_key', 'financial_type', 'amount', 'transaction_date', 'due_date', 'status']);
            }])
            ->withCount([
                'transactions as total_revenue' => function ($query) {
                    $query->where('financial_type', 'revenue')
                        ->where('status', 'done')
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as total_expenses' => function ($query) {
                    $query->where('financial_type', 'expense')
                        ->where('status', 'done')
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as unpaid_installments' => function ($query) {
                    $query->where('status', 'pending')
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                // Month 3 Ago - Weekly Breakdown
                'transactions as month_3_ago_w1' => function ($query) {
                    $date = now()->subMonths(3);
                    $query->whereYear('transaction_date', $date->year)
                        ->whereMonth('transaction_date', $date->month)
                        ->whereDay('transaction_date', '<=', 7)
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as month_3_ago_w2' => function ($query) {
                    $date = now()->subMonths(3);
                    $query->whereYear('transaction_date', $date->year)
                        ->whereMonth('transaction_date', $date->month)
                        ->whereDay('transaction_date', '>', 7)
                        ->whereDay('transaction_date', '<=', 14)
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as month_3_ago_w3' => function ($query) {
                    $date = now()->subMonths(3);
                    $query->whereYear('transaction_date', $date->year)
                        ->whereMonth('transaction_date', $date->month)
                        ->whereDay('transaction_date', '>', 14)
                        ->whereDay('transaction_date', '<=', 21)
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as month_3_ago_w4' => function ($query) {
                    $date = now()->subMonths(3);
                    $query->whereYear('transaction_date', $date->year)
                        ->whereMonth('transaction_date', $date->month)
                        ->whereDay('transaction_date', '>', 21)
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                // Month 2 Ago - Weekly Breakdown
                'transactions as month_2_ago_w1' => function ($query) {
                    $date = now()->subMonths(2);
                    $query->whereYear('transaction_date', $date->year)
                        ->whereMonth('transaction_date', $date->month)
                        ->whereDay('transaction_date', '<=', 7)
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as month_2_ago_w2' => function ($query) {
                    $date = now()->subMonths(2);
                    $query->whereYear('transaction_date', $date->year)
                        ->whereMonth('transaction_date', $date->month)
                        ->whereDay('transaction_date', '>', 7)
                        ->whereDay('transaction_date', '<=', 14)
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as month_2_ago_w3' => function ($query) {
                    $date = now()->subMonths(2);
                    $query->whereYear('transaction_date', $date->year)
                        ->whereMonth('transaction_date', $date->month)
                        ->whereDay('transaction_date', '>', 14)
                        ->whereDay('transaction_date', '<=', 21)
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as month_2_ago_w4' => function ($query) {
                    $date = now()->subMonths(2);
                    $query->whereYear('transaction_date', $date->year)
                        ->whereMonth('transaction_date', $date->month)
                        ->whereDay('transaction_date', '>', 21)
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                // Month 1 Ago - Weekly Breakdown
                'transactions as month_1_ago_w1' => function ($query) {
                    $date = now()->subMonths(1);
                    $query->whereYear('transaction_date', $date->year)
                        ->whereMonth('transaction_date', $date->month)
                        ->whereDay('transaction_date', '<=', 7)
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as month_1_ago_w2' => function ($query) {
                    $date = now()->subMonths(1);
                    $query->whereYear('transaction_date', $date->year)
                        ->whereMonth('transaction_date', $date->month)
                        ->whereDay('transaction_date', '>', 7)
                        ->whereDay('transaction_date', '<=', 14)
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as month_1_ago_w3' => function ($query) {
                    $date = now()->subMonths(1);
                    $query->whereYear('transaction_date', $date->year)
                        ->whereMonth('transaction_date', $date->month)
                        ->whereDay('transaction_date', '>', 14)
                        ->whereDay('transaction_date', '<=', 21)
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as month_1_ago_w4' => function ($query) {
                    $date = now()->subMonths(1);
                    $query->whereYear('transaction_date', $date->year)
                        ->whereMonth('transaction_date', $date->month)
                        ->whereDay('transaction_date', '>', 21)
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                // Current Month - Weekly Breakdown
                'transactions as current_month_w1' => function ($query) {
                    $query->whereYear('transaction_date', now()->year)
                        ->whereMonth('transaction_date', now()->month)
                        ->whereDay('transaction_date', '<=', 7)
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as current_month_w2' => function ($query) {
                    $query->whereYear('transaction_date', now()->year)
                        ->whereMonth('transaction_date', now()->month)
                        ->whereDay('transaction_date', '>', 7)
                        ->whereDay('transaction_date', '<=', 14)
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as current_month_w3' => function ($query) {
                    $query->whereYear('transaction_date', now()->year)
                        ->whereMonth('transaction_date', now()->month)
                        ->whereDay('transaction_date', '>', 14)
                        ->whereDay('transaction_date', '<=', 21)
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as current_month_w4' => function ($query) {
                    $query->whereYear('transaction_date', now()->year)
                        ->whereMonth('transaction_date', now()->month)
                        ->whereDay('transaction_date', '>', 21)
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                // Month 1 Ahead - Weekly Breakdown
                'transactions as month_1_ahead_w1' => function ($query) {
                    $date = now()->addMonths(1);
                    $query->whereYear('due_date', $date->year)
                        ->whereMonth('due_date', $date->month)
                        ->whereDay('due_date', '<=', 7)
                        ->where('status', 'pending')
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as month_1_ahead_w2' => function ($query) {
                    $date = now()->addMonths(1);
                    $query->whereYear('due_date', $date->year)
                        ->whereMonth('due_date', $date->month)
                        ->whereDay('due_date', '>', 7)
                        ->whereDay('due_date', '<=', 14)
                        ->where('status', 'pending')
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as month_1_ahead_w3' => function ($query) {
                    $date = now()->addMonths(1);
                    $query->whereYear('due_date', $date->year)
                        ->whereMonth('due_date', $date->month)
                        ->whereDay('due_date', '>', 14)
                        ->whereDay('due_date', '<=', 21)
                        ->where('status', 'pending')
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as month_1_ahead_w4' => function ($query) {
                    $date = now()->addMonths(1);
                    $query->whereYear('due_date', $date->year)
                        ->whereMonth('due_date', $date->month)
                        ->whereDay('due_date', '>', 21)
                        ->where('status', 'pending')
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                // Month 2 Ahead - Weekly Breakdown
                'transactions as month_2_ahead_w1' => function ($query) {
                    $date = now()->addMonths(2);
                    $query->whereYear('due_date', $date->year)
                        ->whereMonth('due_date', $date->month)
                        ->whereDay('due_date', '<=', 7)
                        ->where('status', 'pending')
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as month_2_ahead_w2' => function ($query) {
                    $date = now()->addMonths(2);
                    $query->whereYear('due_date', $date->year)
                        ->whereMonth('due_date', $date->month)
                        ->whereDay('due_date', '>', 7)
                        ->whereDay('due_date', '<=', 14)
                        ->where('status', 'pending')
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as month_2_ahead_w3' => function ($query) {
                    $date = now()->addMonths(2);
                    $query->whereYear('due_date', $date->year)
                        ->whereMonth('due_date', $date->month)
                        ->whereDay('due_date', '>', 14)
                        ->whereDay('due_date', '<=', 21)
                        ->where('status', 'pending')
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
                'transactions as month_2_ahead_w4' => function ($query) {
                    $date = now()->addMonths(2);
                    $query->whereYear('due_date', $date->year)
                        ->whereMonth('due_date', $date->month)
                        ->whereDay('due_date', '>', 21)
                        ->where('status', 'pending')
                        ->select(DB::raw('COALESCE(SUM(amount), 0)'));
                },
            ])
            ->selectRaw('
                projects.*,
                (
                    COALESCE((
                        SELECT SUM(amount)
                        FROM project_transactions
                        WHERE project_transactions.project_key = projects.key
                        AND financial_type = "revenue"
                        AND status = "done"
                    ), 0) -
                    COALESCE((
                        SELECT SUM(amount)
                        FROM project_transactions
                        WHERE project_transactions.project_key = projects.key
                        AND financial_type = "expense"
                        AND status = "done"
                    ), 0)
                ) as net_cashflow,
                (
                    SELECT MIN(transaction_date)
                    FROM project_transactions
                    WHERE project_transactions.project_key = projects.key
                    AND status = "pending"
                    AND transaction_date > CURDATE()
                ) as next_installment_date
            ');
    }

    /**
     * Get overall company cashflow summary
     */
    public static function getCompanyCashflowSummary(): array
    {
        return Cache::remember('company_cashflow_summary', now()->addMinutes(10), function () {
            // Get project transactions summary - convert to array to avoid PDO serialization
            $projectSummary = DB::table('project_transactions')
                ->where('status', 'done')
                ->selectRaw('
                    SUM(CASE WHEN financial_type = "revenue" THEN amount ELSE 0 END) as total_revenue,
                    SUM(CASE WHEN financial_type = "expense" THEN amount ELSE 0 END) as total_expenses
                ')
                ->first();

            // Get user transactions summary - convert to array to avoid PDO serialization
            $userSummary = DB::table('user_transactions')
                ->where('status', 'done')
                ->selectRaw('
                    SUM(CASE WHEN transaction_type = "deposit" THEN amount ELSE 0 END) as total_deposits,
                    SUM(CASE WHEN transaction_type = "withdraw" THEN amount ELSE 0 END) as total_withdrawals
                ')
                ->first();

            $totalRevenue = (float) ($projectSummary->total_revenue ?? 0);
            $totalExpenses = (float) ($projectSummary->total_expenses ?? 0);
            $totalDeposits = (float) ($userSummary->total_deposits ?? 0);
            $totalWithdrawals = (float) ($userSummary->total_withdrawals ?? 0);

            $currentAvailableCash = $totalRevenue + $totalDeposits - $totalExpenses - $totalWithdrawals;

            return [
                'current_available_cash' => (float) $currentAvailableCash,
                'total_revenue' => $totalRevenue,
                'total_expenses' => $totalExpenses,
                'total_deposits' => $totalDeposits,
                'total_withdrawals' => $totalWithdrawals,
                'net_project_cashflow' => (float) ($totalRevenue - $totalExpenses),
                'net_user_cashflow' => (float) ($totalDeposits - $totalWithdrawals),
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

            // Get monthly project transactions - convert to array to avoid PDO serialization
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

            // Get monthly user transactions - convert to array to avoid PDO serialization
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
