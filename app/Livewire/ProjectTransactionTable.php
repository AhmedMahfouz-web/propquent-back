<?php

namespace App\Livewire;

use App\Models\ProjectTransaction;
use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;
use Filament\Forms;
use Illuminate\Database\Eloquent\Collection;

class ProjectTransactionTable extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;
    
    public $selectedRecords = [];
    public $showSummary = true;

    public function table(Table $table): Table
    {
        return $table
            ->query(ProjectTransaction::query()->with('project.developer'))
            ->striped()
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->selectCurrentPageOnly()
            ->checkIfRecordIsSelectableUsing(fn () => true)
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    })
                    ->filterForm([
                        Forms\Components\Select::make('project_id')
                            ->relationship('project', 'title')
                            ->searchable()
                            ->preload()
                    ])
                    ->filter(function (Builder $query, array $data): Builder {
                        return $query->when($data['project_id'], fn ($q) => $q->where('project_key', Project::find($data['project_id'])?->key));
                    }),

                Tables\Columns\TextColumn::make('project_key')
                    ->label('Project Key')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('project.developer.name')
                    ->label('Developer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('financial_type')
                    ->label('Financial Type')
                    ->formatStateUsing(fn (string $state): string => ProjectTransaction::getAvailableFinancialTypes()[$state] ?? $state)
                    ->colors([
                        'success' => 'revenue',
                        'danger' => 'expense',
                    ])
                    ->sortable()
                    ->filterForm([
                        Forms\Components\Select::make('financial_type')
                            ->options(ProjectTransaction::getAvailableFinancialTypes())
                    ])
                    ->filter(function (Builder $query, array $data): Builder {
                        return $query->when($data['financial_type'], fn ($q) => $q->where('financial_type', $data['financial_type']));
                    }),

                Tables\Columns\TextColumn::make('serving')
                    ->formatStateUsing(fn (?string $state): string => $state ? (ProjectTransaction::getAvailableServingTypes()[$state] ?? $state) : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('what')
                    ->label('What')
                    ->formatStateUsing(fn (?string $state): string => $state ? (ProjectTransaction::getAvailableWhatTypes()[$state] ?? $state) : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('EGP')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold')
                    ->filterForm([
                        Forms\Components\TextInput::make('amount_min')
                            ->label('Min Amount')
                            ->numeric(),
                        Forms\Components\TextInput::make('amount_max')
                            ->label('Max Amount')
                            ->numeric(),
                    ])
                    ->filter(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['amount_min'], fn ($q) => $q->where('amount', '>=', $data['amount_min']))
                            ->when($data['amount_max'], fn ($q) => $q->where('amount', '<=', $data['amount_max']));
                    }),

                Tables\Columns\TextColumn::make('method')
                    ->formatStateUsing(fn (?string $state): string => $state ? (ProjectTransaction::getAvailableTransactionMethods()[$state] ?? $state) : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reference_no')
                    ->label('Reference')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-'),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => ProjectTransaction::getAvailableStatuses()[$state] ?? $state)
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'pending',
                        'danger' => 'cancelled',
                    ])
                    ->sortable()
                    ->filterForm([
                        Forms\Components\Select::make('status')
                            ->options(ProjectTransaction::getAvailableStatuses())
                    ])
                    ->filter(function (Builder $query, array $data): Builder {
                        return $query->when($data['status'], fn ($q) => $q->where('status', $data['status']));
                    }),

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
            ])
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
                    ->label('Edit')
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
                            ->required(),
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
                    
                Tables\Actions\Action::make('quickEdit')
                    ->label('Quick Edit')
                    ->icon('heroicon-m-bolt')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options(fn() => ProjectTransaction::getAvailableStatuses())
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->prefix('EGP'),
                    ])
                    ->fillForm(fn($record) => [
                        'status' => $record->status,
                        'amount' => $record->amount,
                    ])
                    ->action(function ($record, array $data) {
                        $record->update($data);
                        \Filament\Notifications\Notification::make()
                            ->title('Transaction updated successfully')
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-m-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('transaction_date', 'desc');
    }

    public function getTableSummary(): array
    {
        $query = $this->getFilteredTableQuery();
        $records = $query->get();
        
        $totalAmount = $records->sum('amount');
        $totalRevenue = $records->where('financial_type', 'revenue')->sum('amount');
        $totalExpense = $records->where('financial_type', 'expense')->sum('amount');
        $recordCount = $records->count();
        
        return [
            'total_records' => $recordCount,
            'total_amount' => $totalAmount,
            'total_revenue' => $totalRevenue,
            'total_expense' => $totalExpense,
            'net_amount' => $totalRevenue - $totalExpense,
        ];
    }
    
    public function getSelectedSummary(): array
    {
        $selectedIds = $this->getSelectedTableRecords();
        if (empty($selectedIds)) {
            return [
                'selected_records' => 0,
                'selected_amount' => 0,
                'selected_revenue' => 0,
                'selected_expense' => 0,
                'selected_net' => 0,
            ];
        }
        
        $selectedRecords = ProjectTransaction::whereIn('id', $selectedIds)->get();
        
        $selectedAmount = $selectedRecords->sum('amount');
        $selectedRevenue = $selectedRecords->where('financial_type', 'revenue')->sum('amount');
        $selectedExpense = $selectedRecords->where('financial_type', 'expense')->sum('amount');
        
        return [
            'selected_records' => $selectedRecords->count(),
            'selected_amount' => $selectedAmount,
            'selected_revenue' => $selectedRevenue,
            'selected_expense' => $selectedExpense,
            'selected_net' => $selectedRevenue - $selectedExpense,
        ];
    }

    public function render()
    {
        $tableSummary = $this->getTableSummary();
        $selectedSummary = $this->getSelectedSummary();
        
        return view('livewire.project-transaction-table', [
            'tableSummary' => $tableSummary,
            'selectedSummary' => $selectedSummary,
        ]);
    }
}
