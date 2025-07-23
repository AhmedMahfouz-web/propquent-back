<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options([
                        'deposit' => 'Deposit',
                        'withdrawal' => 'Withdrawal',
                        'payment' => 'Payment',
                        'refund' => 'Refund',
                    ])
                    ->required(),
                
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                
                Forms\Components\DatePicker::make('transaction_date')
                    ->required(),
                
                Forms\Components\DatePicker::make('actual_date')
                    ->nullable(),
                
                Forms\Components\Select::make('method')
                    ->options([
                        'cash' => 'Cash',
                        'bank_transfer' => 'Bank Transfer',
                        'cheque' => 'Cheque',
                        'card' => 'Card',
                    ])
                    ->nullable(),
                
                Forms\Components\TextInput::make('reference_no')
                    ->maxLength(255)
                    ->nullable(),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'failed' => 'Failed',
                    ])
                    ->default('pending')
                    ->required(),
                
                Forms\Components\Textarea::make('note')
                    ->maxLength(65535)
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'success' => 'deposit',
                        'danger' => 'withdrawal',
                        'primary' => 'payment',
                        'warning' => 'refund',
                    ]),
                
                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('actual_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('method')
                    ->badge(),
                
                Tables\Columns\TextColumn::make('reference_no')
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                        'secondary' => 'failed',
                    ]),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'deposit' => 'Deposit',
                        'withdrawal' => 'Withdrawal',
                        'payment' => 'Payment',
                        'refund' => 'Refund',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'failed' => 'Failed',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
