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
            ->recordClasses('!py-1 !px-2 text-sm')
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
                    ->extraInputAttributes([
                        'class' => 'text-sm py-1 text-right font-mono',
                        'style' => 'font-family: monospace; text-align: right;'
                    ])
                    ->formatStateUsing(fn ($state) => number_format($state, 2)),

                Tables\Columns\TextColumn::make('method')
                    ->formatStateUsing(fn (?string $state): string => $state ? (ProjectTransaction::getAvailableTransactionMethods()[$state] ?? $state) : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reference_no')
                    ->label('Reference')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-'),

                Tables\Columns\SelectColumn::make('status')
                    ->options(fn() => ProjectTransaction::getAvailableStatuses())
                    ->sortable()
                    ->selectablePlaceholder(false)
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
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-m-trash')
                    ->size('sm')
                    ->color('danger'),
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
