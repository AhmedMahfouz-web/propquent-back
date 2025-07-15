<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\UserTransaction;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserFinancials extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.resources.user-resource.pages.user-financials';

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $title = 'User Financials';

    public function getTableQuery(): Builder
    {
        return UserTransaction::query()
            ->where('user_id', $this->record->id)
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m-01") as month'),
                DB::raw('SUM(CASE WHEN type = \'deposit\' THEN amount ELSE 0 END) as total_deposits'),
                DB::raw('SUM(CASE WHEN type = \'withdraw\' THEN amount ELSE 0 END) as total_withdrawals'),
                DB::raw('SUM(CASE WHEN type = \'deposit\' THEN amount ELSE -amount END) as net_deposit')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('month')
                ->label('Month')
                ->date('F Y'),

            Tables\Columns\TextColumn::make('total_deposits')
                ->label('Total Deposits')
                ->money('usd', true),

            Tables\Columns\TextColumn::make('total_withdrawals')
                ->label('Total Withdrawals')
                ->money('usd', true),

            Tables\Columns\TextColumn::make('net_deposit')
                ->label('Net Deposit')
                ->money('usd', true)
                ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
        ];
    }

    protected function getTableActions(): array
    {
        return [];
    }

    protected function getTableBulkActions(): array
    {
        return [];
    }
}
