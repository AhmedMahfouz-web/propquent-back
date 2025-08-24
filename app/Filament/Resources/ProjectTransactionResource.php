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

    protected static ?string $navigationGroup = 'Transactions';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Details')
                    ->schema([
                        Forms\Components\Select::make('project_key')
                            ->relationship('project', 'title', fn(Builder $query) => $query->with('developer'))
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->title} ({$record->developer->name})")
                            ->searchable(['title', 'key'])
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('financial_type')
                            ->label('Financial Type')
                            ->options(fn() => ProjectTransaction::getAvailableFinancialTypes())
                            ->required(),

                        Forms\Components\Select::make('serving')
                            ->options(fn() => ProjectTransaction::getAvailableServingTypes())
                            ->nullable(),

                        Forms\Components\Select::make('what_id')
                            ->relationship('transactionWhat', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Transaction Category')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->maxLength(65535),
                                Forms\Components\Select::make('category')
                                    ->options([
                                        'payment' => 'Payment',
                                        'fee' => 'Fee',
                                        'charge' => 'Charge',
                                        'deposit' => 'Deposit',
                                    ])
                                    ->nullable(),
                                Forms\Components\Toggle::make('is_active')
                                    ->default(true),
                            ]),
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
                        Forms\Components\DatePicker::make('due_date')
                            ->nullable(),

                        Forms\Components\DatePicker::make('actual_date')
                            ->nullable(),

                        Forms\Components\DatePicker::make('transaction_date')
                            ->required()
                            ->default(today()),
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
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
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

                Tables\Columns\TextColumn::make('project.developer.name')
                    ->label('Developer')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('financial_type')
                    ->label('Type')
                    ->colors([
                        'success' => 'revenue',
                        'danger' => 'expense',
                    ])
                    ->searchable(),

                Tables\Columns\TextColumn::make('serving')
                    ->badge()
                    ->colors([
                        'primary' => 'asset',
                        'info' => 'operation',
                    ])
                    ->toggleable(),

                Tables\Columns\TextColumn::make('transactionWhat.name')
                    ->label('Category')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('USD'),
                    ]),

                Tables\Columns\TextColumn::make('method')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('reference_no')
                    ->label('Reference')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'done',
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('actual_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
                    ->sortable(),

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
                    ->label('Financial Type')
                    ->options(fn() => ProjectTransaction::getAvailableFinancialTypes()),

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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
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
