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
        $summary = CashflowResource::getCompanyCashflowSummary();

        return [
            Stat::make('Available Cash', '$' . number_format($summary['current_available_cash'], 2))
                ->description('Current total')
                ->descriptionIcon($summary['current_available_cash'] >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($summary['current_available_cash'] >= 0 ? 'success' : 'danger')
                ->chart($this->getCashflowTrend()),

            Stat::make('Revenue', '$' . number_format($summary['total_revenue'], 2))
                ->description('Completed')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Expenses', '$' . number_format($summary['total_expenses'], 2))
                ->description('Completed')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('danger'),

            Stat::make('Net Cashflow', '$' . number_format($summary['net_project_cashflow'], 2))
                ->description('Projects')
                ->descriptionIcon($summary['net_project_cashflow'] >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($summary['net_project_cashflow'] >= 0 ? 'success' : 'danger'),

            Stat::make('Deposits', '$' . number_format($summary['total_deposits'], 2))
                ->description('User funds')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('info'),

            Stat::make('Withdrawals', '$' . number_format($summary['total_withdrawals'], 2))
                ->description('User funds')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('warning'),
        ];
    }

    protected function getCashflowTrend(): array
    {
        $monthlyData = CashflowResource::getMonthlyCashflowData(6);
        return array_column($monthlyData, 'running_balance');
    }

    protected static ?int $sort = 1;
}
