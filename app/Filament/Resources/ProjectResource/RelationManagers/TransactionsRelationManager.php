<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\ProjectTransaction;
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
                        'payment' => 'Payment',
                        'refund' => 'Refund',
                        'fee' => 'Fee',
                        'deposit' => 'Deposit',
                    ])
                    ->required(),

                Forms\Components\Select::make('serving')
                    ->options([
                        'buyer' => 'Buyer',
                        'seller' => 'Seller',
                        'agent' => 'Agent',
                        'developer' => 'Developer',
                    ])
                    ->nullable(),

                Forms\Components\Select::make('what_id')
                    ->relationship('transactionWhat', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Transaction Category'),

                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('$')
                    ->required(),

                Forms\Components\DatePicker::make('due_date')
                    ->nullable(),

                Forms\Components\DatePicker::make('actual_date')
                    ->nullable(),

                Forms\Components\DatePicker::make('transaction_date')
                    ->required(),

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
                Tables\Columns\TextColumn::make('financial_type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'primary' => 'revenue',
                        'warning' => 'expense'
                    ]),

                Tables\Columns\TextColumn::make('serving')
                    ->badge(),

                Tables\Columns\TextColumn::make('transactionWhat.name')
                    ->label('What')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
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
                    ]),

                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('actual_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([ProjectTransaction::getProjectType()]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        ProjectTransaction::getProjectStatus()
                    ]),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
