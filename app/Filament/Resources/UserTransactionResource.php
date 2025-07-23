<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserTransactionResource\Pages;
use App\Models\UserTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                        
                        Forms\Components\Select::make('type')
                            ->options([
                                'deposit' => 'Deposit',
                                'withdrawal' => 'Withdrawal',
                                'payment' => 'Payment',
                                'refund' => 'Refund',
                                'bonus' => 'Bonus',
                                'penalty' => 'Penalty',
                                'commission' => 'Commission',
                            ])
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
                            ->options([
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                                'cheque' => 'Cheque',
                                'card' => 'Card',
                                'wire_transfer' => 'Wire Transfer',
                                'cryptocurrency' => 'Cryptocurrency',
                                'paypal' => 'PayPal',
                                'stripe' => 'Stripe',
                            ])
                            ->searchable()
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('reference_no')
                            ->label('Reference Number')
                            ->maxLength(255)
                            ->nullable(),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'failed' => 'Failed',
                                'processing' => 'Processing',
                                'on_hold' => 'On Hold',
                            ])
                            ->default('pending')
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
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')
                    ->searchable(['full_name', 'email'])
                    ->sortable()
                    ->limit(25),
                
                Tables\Columns\TextColumn::make('user.email')
                    ->searchable()
                    ->toggleable()
                    ->limit(30),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'deposit',
                        'danger' => 'withdrawal',
                        'primary' => 'payment',
                        'warning' => 'refund',
                        'info' => 'bonus',
                        'secondary' => 'penalty',
                        'gray' => 'commission',
                    ])
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('USD'),
                        Tables\Columns\Summarizers\Average::make()
                            ->money('USD'),
                    ])
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '-') => 'danger',
                        default => 'success',
                    }),
                
                Tables\Columns\TextColumn::make('method')
                    ->badge()
                    ->colors([
                        'primary' => 'cash',
                        'success' => 'bank_transfer',
                        'warning' => 'cheque',
                        'info' => 'card',
                        'secondary' => 'wire_transfer',
                        'danger' => 'cryptocurrency',
                        'gray' => ['paypal', 'stripe'],
                    ])
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('reference_no')
                    ->label('Reference')
                    ->searchable()
                    ->toggleable()
                    ->copyable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                        'secondary' => 'failed',
                        'info' => 'processing',
                        'gray' => 'on_hold',
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('actual_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
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
                    ->options([
                        'deposit' => 'Deposit',
                        'withdrawal' => 'Withdrawal',
                        'payment' => 'Payment',
                        'refund' => 'Refund',
                        'bonus' => 'Bonus',
                        'penalty' => 'Penalty',
                        'commission' => 'Commission',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'failed' => 'Failed',
                        'processing' => 'Processing',
                        'on_hold' => 'On Hold',
                    ]),
                
                Tables\Filters\SelectFilter::make('method')
                    ->options([
                        'cash' => 'Cash',
                        'bank_transfer' => 'Bank Transfer',
                        'cheque' => 'Cheque',
                        'card' => 'Card',
                        'wire_transfer' => 'Wire Transfer',
                        'cryptocurrency' => 'Cryptocurrency',
                        'paypal' => 'PayPal',
                        'stripe' => 'Stripe',
                    ]),
                
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
                                fn (Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['amount_to'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
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
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
