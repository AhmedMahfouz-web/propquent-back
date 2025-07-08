<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UserTransactionResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestUserTransactions extends BaseWidget
{
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(UserTransactionResource::getEloquentQuery()->with('user'))
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('User'),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('usd', true),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->date(),
            ]);
    }
}
