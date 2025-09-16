<?php

namespace App\Filament\Resources\CashflowResource\Widgets;

use App\Filament\Resources\CashflowResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CashflowOverviewWidget extends BaseWidget
{
    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 2,
    ];

    protected function getStats(): array
    {
        $summary = CashflowResource::getCashflowSummary();

        return [
            Stat::make('Current Balance', '$' . number_format($summary['current_balance'], 2))
                ->description('Available cash today')
                ->descriptionIcon($summary['current_balance'] >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($summary['current_balance'] >= 0 ? 'success' : 'danger'),

            Stat::make('Expected In (30 days)', '$' . number_format($summary['pending_in_30_days'], 2))
                ->description('Pending revenue & deposits')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('success'),

            Stat::make('Expected Out (30 days)', '$' . number_format($summary['pending_out_30_days'], 2))
                ->description('Pending expenses & withdrawals')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('danger'),

            Stat::make('Projected Balance (30 days)', '$' . number_format($summary['projected_balance_30_days'], 2))
                ->description('Expected balance in 30 days')
                ->descriptionIcon($summary['projected_balance_30_days'] >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($summary['projected_balance_30_days'] >= 0 ? 'success' : 'danger'),
        ];
    }

    protected static ?int $sort = 1;
}
