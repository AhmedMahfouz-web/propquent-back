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

class ProjectTransactionTable extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->query(ProjectTransaction::query()->with('project.developer'))
            ->striped()
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
            ])
            ->columns([
                Tables\Columns\SelectColumn::make('project_key')
                    ->label('Project')
                    ->options(function () {
                        return Project::with('developer')
                            ->get()
                            ->mapWithKeys(function ($project) {
                                return [$project->key => "{$project->title} ({$project->developer->name})"];
                            })
                            ->toArray();
                    })
                    ->rules(['required', 'exists:projects,key'])
                    ->selectablePlaceholder(false)
                    ->searchable()
                    ->sortable()
                    ->width(200),

                Tables\Columns\SelectColumn::make('financial_type')
                    ->label('Financial Type')
                    ->options(fn() => ProjectTransaction::getAvailableFinancialTypes())
                    ->rules(['required'])
                    ->selectablePlaceholder(false)
                    ->searchable()
                    ->sortable()
                    ->width(150),

                Tables\Columns\SelectColumn::make('serving')
                    ->options(fn() => ProjectTransaction::getAvailableServingTypes())
                    ->placeholder('Select serving...')
                    ->selectablePlaceholder(false)
                    ->sortable(),

                Tables\Columns\SelectColumn::make('what')
                    ->label('What')
                    ->options(fn() => ProjectTransaction::getAvailableWhatTypes())
                    ->placeholder('Select purpose...')
                    ->selectablePlaceholder(false)
                    ->sortable()
                    ->width(150),

                Tables\Columns\TextInputColumn::make('amount')
                    ->extraInputAttributes([
                        'type' => 'number',
                        'step' => '0.01',
                        'required' => true
                    ])
                    ->rules(['required', 'numeric', 'min:0.01'])
                    ->placeholder('0.00')
                    ->sortable()
                    ->width(120),

                Tables\Columns\SelectColumn::make('method')
                    ->options(fn() => ProjectTransaction::getAvailableTransactionMethods())
                    ->placeholder('Select method...')
                    ->selectablePlaceholder(false)
                    ->sortable(),

                Tables\Columns\TextInputColumn::make('reference_no')
                    ->label('Reference')
                    ->placeholder('Reference number...')
                    ->rules(['max:255'])
                    ->sortable()
                    ->width(150),

                Tables\Columns\SelectColumn::make('status')
                    ->options(fn() => ProjectTransaction::getAvailableStatuses())
                    ->rules(['required'])
                    ->selectablePlaceholder(false)
                    ->sortable()
                    ->width(120),

                Tables\Columns\TextInputColumn::make('transaction_date')
                    ->rules(['required', 'date_format:Y-m-d'])
                    ->placeholder('YYYY-MM-DD')
                    ->extraInputAttributes([
                        'type' => 'text',
                        'pattern' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
                        'required' => true
                    ])
                    ->width(150)
                    ->sortable(),

                Tables\Columns\TextInputColumn::make('due_date')
                    ->rules(['nullable', 'date_format:Y-m-d'])
                    ->placeholder('YYYY-MM-DD')
                    ->extraInputAttributes([
                        'type' => 'text',
                        'pattern' => '[0-9]{4}-[0-9]{2}-[0-9]{2}'
                    ])
                    ->sortable()
                    ->width(150),

                Tables\Columns\TextInputColumn::make('actual_date')
                    ->rules(['nullable', 'date_format:Y-m-d'])
                    ->placeholder('YYYY-MM-DD')
                    ->extraInputAttributes([
                        'type' => 'text',
                        'pattern' => '[0-9]{4}-[0-9]{2}-[0-9]{2}'
                    ])
                    ->sortable()
                    ->width(150),

                Tables\Columns\TextInputColumn::make('note')
                    ->placeholder('Add note...')
                    ->rules(['max:65535'])
                    ->sortable()
                    ->width(200),
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
                    ->url(asset('templates/project-transactions-template.xlsx'))
                    ->openUrlInNewTab()
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('transaction_date', 'desc');
    }

    public function render()
    {
        return view('livewire.project-transaction-table');
    }
}
