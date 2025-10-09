<?php

namespace App\Livewire;

use App\Models\UserTransaction;
use App\Models\User;
use Livewire\Component;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;

class UserTransactionTable extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;
    
    public $showSummary = true;
    public $customSelectedRecords = [];
    
    protected $listeners = [
        'updateSelectedSummary' => '$refresh',
        'tableSelectionChanged' => 'updateSelectedRecords'
    ];

    #[On('updateSelectedSummary')]
    public function refreshSelectedSummary()
    {
        $this->dispatch('$refresh');
    }
    
    public function updateSelectedRecords($selectedIds)
    {
        $this->customSelectedRecords = $selectedIds;
    }
    
    // Make summaries computed properties that react to changes
    public function getTableSummaryProperty()
    {
        return $this->getTableSummary();
    }
    
    public function getSelectedSummaryProperty()
    {
        return $this->getSelectedSummary();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(UserTransaction::query()->with(['user']))
            ->striped()
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->selectCurrentPageOnly()
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('User')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
                    
                Tables\Columns\TextColumn::make('transaction_type')
                    ->label('Type')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'deposit' => 'success',
                        'withdraw' => 'danger',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('EGP')
                    ->sortable()
                    ->alignEnd()
                    ->color(fn ($record): string => $record->transaction_type === 'withdraw' ? 'danger' : 'success'),
                    
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Transaction Date')
                    ->date('M j, Y')
                    ->sortable()
                    ->searchable(isIndividual: true),
                    
                Tables\Columns\TextColumn::make('actual_date')
                    ->label('Actual Date')
                    ->date('M j, Y')
                    ->sortable()
                    ->placeholder('Not set'),
                    
                Tables\Columns\TextColumn::make('method')
                    ->label('Method')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->badge()
                    ->placeholder('Not specified'),
                    
                Tables\Columns\TextColumn::make('reference_no')
                    ->label('Reference')
                    ->searchable(isIndividual: true)
                    ->limit(20)
                    ->placeholder('No reference'),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('note')
                    ->label('Note')
                    ->searchable(isIndividual: true)
                    ->limit(30)
                    ->placeholder('No note')
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'full_name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('transaction_type')
                    ->label('Transaction Type')
                    ->options(UserTransaction::getAvailableTransactionTypes()),
                    
                Tables\Filters\SelectFilter::make('method')
                    ->label('Method')
                    ->options(UserTransaction::getAvailableMethods()),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(UserTransaction::getAvailableStatuses()),
                    
                Tables\Filters\Filter::make('amount_range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('amount_from')
                                    ->label('Amount From')
                                    ->numeric()
                                    ->placeholder('Min amount'),
                                Forms\Components\TextInput::make('amount_to')
                                    ->label('Amount To')
                                    ->numeric()
                                    ->placeholder('Max amount'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['amount_to'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['amount_from'] ?? null) {
                            $indicators[] = 'Amount from: ' . number_format($data['amount_from'], 2);
                        }
                        if ($data['amount_to'] ?? null) {
                            $indicators[] = 'Amount to: ' . number_format($data['amount_to'], 2);
                        }
                        return $indicators;
                    }),
                    
                Tables\Filters\Filter::make('transaction_date_range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('transaction_date_from')
                                    ->label('Transaction Date From'),
                                Forms\Components\DatePicker::make('transaction_date_to')
                                    ->label('Transaction Date To'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['transaction_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['transaction_date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['transaction_date_from'] ?? null) {
                            $indicators[] = 'Transaction date from: ' . \Carbon\Carbon::parse($data['transaction_date_from'])->format('M j, Y');
                        }
                        if ($data['transaction_date_to'] ?? null) {
                            $indicators[] = 'Transaction date to: ' . \Carbon\Carbon::parse($data['transaction_date_to'])->format('M j, Y');
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color('primary'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('transaction_date', 'desc');
    }

    protected function getTableQueryForSummary()
    {
        try {
            // Use Filament's built-in method to get filtered query
            return $this->getFilteredTableQuery();
        } catch (\Exception $e) {
            // Fallback to base query if table methods fail
            return UserTransaction::query()->with('user');
        }
    }

    public function getTableSummary(): array
    {
        try {
            // Get the current page records from the table
            $table = $this->getTable();
            $records = $table->getRecords();
            
            // Calculate from current page records only
            $recordCount = $records->count();
            $totalAmount = $records->sum('amount');
            $totalDeposit = $records->where('transaction_type', 'deposit')->sum('amount');
            $totalWithdraw = $records->where('transaction_type', 'withdraw')->sum('amount');
            
            return [
                'total_records' => $recordCount,
                'total_amount' => $totalAmount,
                'total_deposit' => $totalDeposit,
                'total_withdraw' => $totalWithdraw,
                'net_amount' => $totalDeposit - $totalWithdraw,
            ];
        } catch (\Exception $e) {
            return [
                'total_records' => 0,
                'total_amount' => 0,
                'total_deposit' => 0,
                'total_withdraw' => 0,
                'net_amount' => 0,
            ];
        }
    }
    
    public function getSelectedSummary(): array
    {
        try {
            // Get selected records using efficient database queries
            $selectedIds = [];
            
            // Try multiple approaches to get selected record IDs
            if (!empty($this->customSelectedRecords)) {
                $selectedIds = $this->customSelectedRecords;
            } else {
                // Try to get selected records from table
                try {
                    $selectedRecords = $this->getSelectedTableRecords();
                    if (is_array($selectedRecords)) {
                        $selectedIds = $selectedRecords;
                    }
                } catch (\Exception $e) {
                    $selectedIds = [];
                }
            }
            
            if (empty($selectedIds)) {
                return [
                    'selected_records' => 0,
                    'selected_amount' => 0,
                    'selected_deposit' => 0,
                    'selected_withdraw' => 0,
                    'selected_net' => 0,
                ];
            }
            
            // Use database aggregation for better performance
            $query = UserTransaction::whereIn('id', $selectedIds);
            $selectedCount = $query->count();
            $selectedAmount = $query->sum('amount');
            $selectedDeposit = $query->where('transaction_type', 'deposit')->sum('amount');
            $selectedWithdraw = $query->where('transaction_type', 'withdraw')->sum('amount');
            
            return [
                'selected_records' => $selectedCount,
                'selected_amount' => $selectedAmount,
                'selected_deposit' => $selectedDeposit,
                'selected_withdraw' => $selectedWithdraw,
                'selected_net' => $selectedDeposit - $selectedWithdraw,
            ];
        } catch (\Exception $e) {
            return [
                'selected_records' => 0,
                'selected_amount' => 0,
                'selected_deposit' => 0,
                'selected_withdraw' => 0,
                'selected_net' => 0,
            ];
        }
    }

    public function render()
    {
        return view('livewire.user-transaction-table', [
            'tableSummary' => $this->tableSummary,
            'selectedSummary' => $this->selectedSummary,
        ]);
    }
}
