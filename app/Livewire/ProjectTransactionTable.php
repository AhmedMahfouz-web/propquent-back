<?php

namespace App\Livewire;

use App\Models\ProjectTransaction;
use App\Models\Project;
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
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\On;

class ProjectTransactionTable extends Component implements HasTable, HasForms
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
    
    public function confirmAmountChange($recordId, $newAmount, $oldAmount)
    {
        $this->dispatch('openAmountConfirmModal', [
            'recordId' => $recordId,
            'newAmount' => $newAmount,
            'oldAmount' => $oldAmount
        ]);
    }
    
    public function updateAmount($recordId, $newAmount)
    {
        try {
            $transaction = ProjectTransaction::findOrFail($recordId);
            $transaction->update(['amount' => $newAmount]);
            
            \Filament\Notifications\Notification::make()
                ->title('Amount Updated')
                ->body("Amount updated to " . number_format($newAmount, 2))
                ->success()
                ->send();
                
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Error')
                ->body('Failed to update amount')
                ->danger()
                ->send();
        }
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
            ->query(ProjectTransaction::query()->with(['project.developer']))
            ->striped()
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->selectCurrentPageOnly()
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
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

                Tables\Columns\TextColumn::make('project_key')
                    ->label('Project Key')
                    ->searchable(isIndividual: true)
                    ->copyable(),

                Tables\Columns\TextColumn::make('project.developer.name')
                    ->label('Developer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(isIndividual: true),

                Tables\Columns\BadgeColumn::make('financial_type')
                    ->label('Financial Type')
                    ->formatStateUsing(fn (string $state): string => ProjectTransaction::getAvailableFinancialTypes()[$state] ?? $state)
                    ->colors([
                        'success' => 'revenue',
                        'danger' => 'expense',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('serving')
                    ->formatStateUsing(fn (?string $state): string => $state ? (ProjectTransaction::getAvailableServingTypes()[$state] ?? $state) : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('what')
                    ->label('What')
                    ->formatStateUsing(fn (?string $state): string => $state ? (ProjectTransaction::getAvailableWhatTypes()[$state] ?? $state) : '-')
                    ->sortable(),

                Tables\Columns\TextInputColumn::make('amount')
                    ->type('number')
                    ->step(0.01)
                    ->sortable()
                    ->alignEnd()
                    ->rules(['required', 'numeric', 'min:0.01'])
                    ->extraInputAttributes(function ($record) {
                        return [
                            'class' => 'text-sm py-1 text-right font-mono',
                            'style' => 'font-family: monospace; text-align: right;',
                            'data-original-value' => $record->amount ?? '',
                            'data-record-id' => $record->id ?? '',
                        ];
                    }),

                Tables\Columns\TextColumn::make('method')
                    ->formatStateUsing(fn (?string $state): string => $state ? (ProjectTransaction::getAvailableTransactionMethods()[$state] ?? $state) : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reference_no')
                    ->label('Reference')
                    ->searchable(isIndividual: true)
                    ->copyable()
                    ->placeholder('-'),

                Tables\Columns\SelectColumn::make('status')
                    ->options(fn() => ProjectTransaction::getAvailableStatuses())
                    ->sortable()
                    ->extraAttributes(['class' => 'text-sm']),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('actual_date')
                    ->date()
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('note')
                    ->limit(50)
                    ->searchable(isIndividual: true)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('financial_type')
                    ->options(fn() => ProjectTransaction::getAvailableFinancialTypes()),

                Tables\Filters\SelectFilter::make('serving')
                    ->options(fn() => ProjectTransaction::getAvailableServingTypes()),

                Tables\Filters\SelectFilter::make('what')
                    ->label('What (Purpose)')
                    ->options(fn() => ProjectTransaction::getAvailableWhatTypes()),

                Tables\Filters\SelectFilter::make('status')
                    ->options(fn() => ProjectTransaction::getAvailableStatuses()),

                Tables\Filters\SelectFilter::make('method')
                    ->options(fn() => ProjectTransaction::getAvailableTransactionMethods()),

                Tables\Filters\Filter::make('amount_range')
                    ->form([
                        Forms\Components\TextInput::make('amount_from')
                            ->label('Min Amount')
                            ->numeric()
                            ->placeholder('0.00'),
                        Forms\Components\TextInput::make('amount_to')
                            ->label('Max Amount')
                            ->numeric()
                            ->placeholder('1000.00'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn(Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['amount_to'],
                                fn(Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
                            );
                    }),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    }),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->persistFiltersInSession()
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add New Row')
                    ->form([
                        \Filament\Forms\Components\Select::make('project_key')
                            ->label('Project')
                            ->options(function () {
                                return Project::with('developer')
                                    ->get()
                                    ->mapWithKeys(function ($project) {
                                        return [$project->key => "{$project->title} ({$project->developer->name})"];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->required(),
                        \Filament\Forms\Components\Select::make('financial_type')
                            ->label('Financial Type')
                            ->options(fn() => ProjectTransaction::getAvailableFinancialTypes())
                            ->required(),
                        \Filament\Forms\Components\Select::make('serving')
                            ->options(fn() => ProjectTransaction::getAvailableServingTypes())
                            ->nullable(),
                        \Filament\Forms\Components\Select::make('what')
                            ->label('What (Purpose)')
                            ->options(fn() => ProjectTransaction::getAvailableWhatTypes())
                            ->nullable(),
                        \Filament\Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->required()
                            ->rules(['min:0.01']),
                        \Filament\Forms\Components\Select::make('method')
                            ->options(fn() => ProjectTransaction::getAvailableTransactionMethods())
                            ->nullable(),
                        \Filament\Forms\Components\TextInput::make('reference_no')
                            ->label('Reference Number')
                            ->maxLength(255)
                            ->nullable(),
                        \Filament\Forms\Components\Select::make('status')
                            ->options(fn() => ProjectTransaction::getAvailableStatuses())
                            ->default('pending')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('transaction_date')
                            ->placeholder('YYYY-MM-DD')
                            ->rules(['required', 'date_format:Y-m-d'])
                            ->required()
                            ->default(today()->format('Y-m-d')),
                        \Filament\Forms\Components\TextInput::make('due_date')
                            ->placeholder('YYYY-MM-DD')
                            ->rules(['nullable', 'date_format:Y-m-d'])
                            ->nullable(),
                        \Filament\Forms\Components\TextInput::make('actual_date')
                            ->placeholder('YYYY-MM-DD')
                            ->rules(['nullable', 'date_format:Y-m-d'])
                            ->nullable(),
                        \Filament\Forms\Components\Textarea::make('note')
                            ->maxLength(65535)
                            ->nullable(),
                    ]),
                Tables\Actions\Action::make('import')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        \Filament\Forms\Components\FileUpload::make('file')
                            ->label('Excel File')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->required()
                            ->helperText('Upload an Excel file with project transaction data')
                    ])
                    ->action(function (array $data) {
                        try {
                            $filePath = storage_path('app/public/' . $data['file']);
                            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\ProjectTransactionImport, $filePath);
                            \Filament\Notifications\Notification::make()
                                ->title('Import Successful')
                                ->body('Project transactions have been imported successfully.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Import Failed')
                                ->body('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('downloadTemplate')
                    ->label('Download Template')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        // Generate fresh template with current data
                        Artisan::call('template:project-transactions');
                        
                        // Return the generated file for download
                        $templatePath = public_path('templates/project-transactions-template.xlsx');
                        
                        if (file_exists($templatePath)) {
                            return response()->download($templatePath, 'project-transactions-template-' . date('Y-m-d') . '.xlsx');
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Error')
                                ->body('Failed to generate template. Please try again.')
                                ->danger()
                                ->send();
                        }
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('project_key')
                            ->label('Project')
                            ->options(function () {
                                return Project::with('developer')
                                    ->get()
                                    ->mapWithKeys(function ($project) {
                                        return [$project->key => "{$project->title} ({$project->developer->name})"];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('financial_type')
                            ->label('Financial Type')
                            ->options(fn() => ProjectTransaction::getAvailableFinancialTypes())
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('EGP')
                            ->step(0.01)
                            ->required()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                // Show confirmation for amount changes
                                $this->dispatch('confirmAmountChange', [
                                    'newAmount' => $state,
                                    'recordId' => $get('id')
                                ]);
                            }),
                        Forms\Components\Select::make('status')
                            ->options(fn() => ProjectTransaction::getAvailableStatuses())
                            ->required(),
                        Forms\Components\Select::make('serving')
                            ->options(fn() => ProjectTransaction::getAvailableServingTypes())
                            ->nullable(),
                        Forms\Components\Select::make('what')
                            ->options(fn() => ProjectTransaction::getAvailableWhatTypes())
                            ->nullable(),
                        Forms\Components\Select::make('method')
                            ->options(fn() => ProjectTransaction::getAvailableTransactionMethods())
                            ->nullable(),
                        Forms\Components\TextInput::make('reference_no')
                            ->label('Reference Number')
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\DatePicker::make('transaction_date')
                            ->required(),
                        Forms\Components\DatePicker::make('due_date')
                            ->nullable(),
                        Forms\Components\DatePicker::make('actual_date')
                            ->nullable(),
                        Forms\Components\Textarea::make('note')
                            ->maxLength(65535)
                            ->nullable(),
                    ]),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-m-trash')
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
            return ProjectTransaction::query()->with('project.developer');
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
            $totalRevenue = $records->where('financial_type', 'revenue')->sum('amount');
            $totalExpense = $records->where('financial_type', 'expense')->sum('amount');
            
            return [
                'total_records' => $recordCount,
                'total_amount' => $totalAmount,
                'total_revenue' => $totalRevenue,
                'total_expense' => $totalExpense,
                'net_amount' => $totalRevenue - $totalExpense,
            ];
        } catch (\Exception $e) {
            return [
                'total_records' => 0,
                'total_amount' => 0,
                'total_revenue' => 0,
                'total_expense' => 0,
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
                    'selected_revenue' => 0,
                    'selected_expense' => 0,
                    'selected_net' => 0,
                ];
            }
            
            // Use database aggregation for better performance
            $query = ProjectTransaction::whereIn('id', $selectedIds);
            $selectedCount = $query->count();
            $selectedAmount = $query->sum('amount');
            $selectedRevenue = $query->where('financial_type', 'revenue')->sum('amount');
            $selectedExpense = $query->where('financial_type', 'expense')->sum('amount');
            
            return [
                'selected_records' => $selectedCount,
                'selected_amount' => $selectedAmount,
                'selected_revenue' => $selectedRevenue,
                'selected_expense' => $selectedExpense,
                'selected_net' => $selectedRevenue - $selectedExpense,
            ];
        } catch (\Exception $e) {
            return [
                'selected_records' => 0,
                'selected_amount' => 0,
                'selected_revenue' => 0,
                'selected_expense' => 0,
                'selected_net' => 0,
            ];
        }
    }

    public function render()
    {
        return view('livewire.project-transaction-table', [
            'tableSummary' => $this->tableSummary,
            'selectedSummary' => $this->selectedSummary,
        ]);
    }
}
