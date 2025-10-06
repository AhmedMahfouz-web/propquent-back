<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserTransactionResource\Pages;
use App\Models\UserTransaction;
use App\Imports\UserTransactionImport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class UserTransactionResource extends Resource
{
    protected static ?string $model = UserTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Transactions';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'full_name')
                            ->searchable(['full_name', 'email'])
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('transaction_type')
                            ->label('Type')
                            ->options(fn() => UserTransaction::getAvailableTransactionTypes() ?: [])
                            ->required(),

                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->required()
                            ->rules(['min:0.01']),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\Select::make('method')
                            ->options(fn() => UserTransaction::getAvailableMethods() ?: [])
                            ->nullable()
                            ->searchable(),

                        Forms\Components\TextInput::make('reference_no')
                            ->label('Reference Number')
                            ->maxLength(255)
                            ->nullable(),

                        Forms\Components\Select::make('status')
                            ->options(fn() => UserTransaction::getAvailableStatuses() ?: [])
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Date Information')
                    ->schema([
                        Forms\Components\DatePicker::make('transaction_date')
                            ->required()
                            ->default(today()),

                        Forms\Components\DatePicker::make('actual_date')
                            ->nullable()
                            ->helperText('Date when transaction was actually processed'),
                    ])
                    ->columns(2),

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
                Tables\Columns\SelectColumn::make('user_id')
                    ->label('User')
                    ->options(function () {
                        return \App\Models\User::all()
                            ->mapWithKeys(function ($user) {
                                return [$user->id => "{$user->full_name} ({$user->email})"];
                            })
                            ->toArray();
                    })
                    ->rules(['required', 'exists:users,id'])
                    ->selectablePlaceholder(false)
                    ->searchable()
                    ->sortable()
                    ->width(250),

                Tables\Columns\SelectColumn::make('transaction_type')
                    ->label('Type')
                    ->options(fn() => UserTransaction::getAvailableTransactionTypes())
                    ->rules(['required'])
                    ->selectablePlaceholder(false)
                    ->searchable()
                    ->sortable()
                    ->width(120),

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
                    ->options(fn() => UserTransaction::getAvailableMethods() ?: [])
                    ->placeholder('Select method...')
                    ->selectablePlaceholder(false)
                    ->sortable()
                    ->width(150),

                Tables\Columns\TextInputColumn::make('reference_no')
                    ->label('Reference')
                    ->placeholder('Reference number...')
                    ->rules(['max:255'])
                    ->sortable()
                    ->width(150),

                Tables\Columns\SelectColumn::make('status')
                    ->options(fn() => UserTransaction::getAvailableStatuses() ?: [])
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

                // Read-only columns for reference
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('User Name')
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

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'full_name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('type')
                    ->options(fn() => UserTransaction::getAvailableTransactionTypes() ?: []),

                Tables\Filters\SelectFilter::make('status')
                    ->options(fn() => UserTransaction::getAvailableStatuses() ?: []),

                Tables\Filters\SelectFilter::make('method')
                    ->options(fn() => UserTransaction::getAvailableMethods() ?: []),

                Tables\Filters\Filter::make('amount_range')
                    ->form([
                        Forms\Components\TextInput::make('amount_from')
                            ->numeric()
                            ->prefix('$')
                            ->label('Amount From'),
                        Forms\Components\TextInput::make('amount_to')
                            ->numeric()
                            ->prefix('$')
                            ->label('Amount To'),
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
                            ->label('Date From'),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('Date To'),
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
                            ->helperText('Upload an Excel file with columns: user_id, type, amount, transaction_date, description, status')
                    ])
                    ->action(function (array $data) {
                        try {
                            $filePath = storage_path('app/public/' . $data['file']);
                            Excel::import(new UserTransactionImport, $filePath);
                            \Filament\Notifications\Notification::make()
                                ->title('Import Successful')
                                ->body('User transactions have been imported successfully.')
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
                    ->url(asset('templates/user-transactions-template.xlsx'))
                    ->openUrlInNewTab()
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete User Transaction')
                    ->modalDescription('Are you sure you want to delete this transaction? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete it'),
            ])
            ->recordUrl(null) // Disable row click navigation to allow inline editing
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Selected Transactions')
                        ->modalDescription('Are you sure you want to delete the selected transactions? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete them'),
                    Tables\Actions\BulkAction::make('mark_completed')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'completed']);
                            });
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('mark_cancelled')
                        ->label('Mark as Cancelled')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'cancelled']);
                            });
                        })
                        ->requiresConfirmation(),
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
            'index' => Pages\ListUserTransactions::route('/'),
            'create' => Pages\CreateUserTransaction::route('/create'),
            'view' => Pages\ViewUserTransaction::route('/{record}'),
            'edit' => Pages\EditUserTransaction::route('/{record}/edit'),
        ];
    }
}
