<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProjectTransactionResource;
use App\Models\ProjectTransaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestProjectTransactions extends BaseWidget
{
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProjectTransaction::query()
                    ->with('project')
                    ->latest()
                    ->limit(10)
            )
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project'),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('usd', true),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->date(),
            ]);
    }
}
