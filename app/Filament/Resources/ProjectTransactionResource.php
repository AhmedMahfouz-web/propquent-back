<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectTransactionResource\Pages;
use App\Models\ProjectTransaction;
use App\Imports\ProjectTransactionImport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\ImportAction;
use Maatwebsite\Excel\Facades\Excel;

class ProjectTransactionResource extends Resource
{
    protected static ?string $model = ProjectTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Project Transactions';

    protected static ?string $navigationGroup = 'Transactions';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Details')
                    ->schema([
                        Forms\Components\Select::make('project_key')
                            ->label('Project')
                            ->options(function () {
                                return \App\Models\Project::with('developer')
                                    ->get()
                                    ->mapWithKeys(function ($project) {
                                        return [$project->key => "{$project->title} ({$project->developer->name})"];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->required()
                            ->columnSpanFull(),


                        Forms\Components\Select::make('serving')
                            ->options(fn() => ProjectTransaction::getAvailableServingTypes())
                            ->nullable(),

                        Forms\Components\TextInput::make('transaction_category')
                            ->label('Transaction Category')
                            ->maxLength(255)
                            ->placeholder('Enter transaction category')
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Financial Information')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->required()
                            ->rules(['min:0.01']),

                        Forms\Components\Select::make('method')
                            ->options(fn() => ProjectTransaction::getAvailableTransactionMethods())
                            ->searchable()
                            ->nullable(),

                        Forms\Components\TextInput::make('reference_no')
                            ->label('Reference Number')
                            ->maxLength(255)
                            ->nullable(),

                        Forms\Components\Select::make('status')
                            ->options(fn() => ProjectTransaction::getAvailableStatuses())
                            ->default('pending')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Date Information')
                    ->schema([
                        Forms\Components\TextInput::make('due_date')
                            ->placeholder('YYYY-MM-DD')
                            ->rules(['nullable', 'date_format:Y-m-d'])
                            ->nullable(),

                        Forms\Components\TextInput::make('actual_date')
                            ->placeholder('YYYY-MM-DD')
                            ->rules(['nullable', 'date_format:Y-m-d'])
                            ->nullable(),

                        Forms\Components\TextInput::make('transaction_date')
                            ->placeholder('YYYY-MM-DD')
                            ->rules(['required', 'date_format:Y-m-d'])
                            ->required()
                            ->default(today()->format('Y-m-d')),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('note')
                            ->maxLength(65535)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
            ])
            ->striped()
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\SelectColumn::make('project_key')
                    ->label('Project')
                    ->options(function () {
                        return \App\Models\Project::with('developer')
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

                Tables\Columns\SelectColumn::make('serving')
                    ->options(fn() => ProjectTransaction::getAvailableServingTypes())
                    ->placeholder('Select serving...')
                    ->selectablePlaceholder(false),

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
                    ->selectablePlaceholder(false),

                Tables\Columns\TextInputColumn::make('reference_no')
                    ->label('Reference')
                    ->placeholder('Reference number...')
                    ->rules(['max:255'])
                    ->width(150),

                Tables\Columns\SelectColumn::make('status')
                    ->options(fn() => ProjectTransaction::getAvailableStatuses())
                    ->rules(['required'])
                    ->selectablePlaceholder(false)
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
                    ->width(150),

                Tables\Columns\TextInputColumn::make('actual_date')
                    ->rules(['nullable', 'date_format:Y-m-d'])
                    ->placeholder('YYYY-MM-DD')
                    ->extraInputAttributes([
                        'type' => 'text',
                        'pattern' => '[0-9]{4}-[0-9]{2}-[0-9]{2}'
                    ])
                    ->width(150),

                Tables\Columns\TextInputColumn::make('note')
                    ->placeholder('Add note...')
                    ->rules(['max:65535'])
                    ->width(200),
                // Read-only columns for existing records
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project Title')
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
                    ->toggleable(),

                Tables\Columns\TextColumn::make('project.developer.name')
                    ->label('Developer')
                    ->searchable()
                    ->toggleable(),

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


                Tables\Filters\SelectFilter::make('serving')
                    ->options(fn() => ProjectTransaction::getAvailableServingTypes()),

                Tables\Filters\SelectFilter::make('status')
                    ->options(fn() => ProjectTransaction::getAvailableStatuses()),

                Tables\Filters\SelectFilter::make('method')
                    ->options(fn() => ProjectTransaction::getAvailableTransactionMethods()),

                Tables\Filters\Filter::make('amount_range')
                    ->form([
                        Forms\Components\TextInput::make('amount_from')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('amount_to')
                            ->numeric()
                            ->prefix('$'),
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
                        Forms\Components\DatePicker::make('date_from'),
                        Forms\Components\DatePicker::make('date_to'),
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add New Row')
                    ->keyBindings(['ctrl+n', 'cmd+n']),
                Tables\Actions\Action::make('import')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->label('Excel File')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->required()
                            ->helperText('Upload an Excel file with columns: project_key, type, category, amount, transaction_date, description')
                    ])
                    ->action(function (array $data) {
                        try {
                            $filePath = storage_path('app/public/' . $data['file']);
                            Excel::import(new ProjectTransactionImport, $filePath);
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
            ->recordUrl(null) // Disable row click navigation to allow inline editing
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('transaction_date', 'desc');
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
            'index' => Pages\ListProjectTransactions::route('/'),
            'create' => Pages\CreateProjectTransaction::route('/create'),
            'view' => Pages\ViewProjectTransaction::route('/{record}'),
            'edit' => Pages\EditProjectTransaction::route('/{record}/edit'),
        ];
    }
}
