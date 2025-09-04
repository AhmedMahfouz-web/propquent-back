<?php

namespace App\Filament\Resources\CashflowResource\Widgets;

use App\Filament\Resources\CashflowResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CashflowOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $summary = CashflowResource::getCompanyCashflowSummary();

        return [
            Stat::make('Current Available Cash', '$' . number_format($summary['current_available_cash'], 2))
                ->description('Total cash available right now')
                ->descriptionIcon($summary['current_available_cash'] >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($summary['current_available_cash'] >= 0 ? 'success' : 'danger')
                ->chart($this->getCashflowTrend()),

            Stat::make('Total Revenue', '$' . number_format($summary['total_revenue'], 2))
                ->description('All completed revenue transactions')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Total Expenses', '$' . number_format($summary['total_expenses'], 2))
                ->description('All completed expense transactions')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('danger'),

            Stat::make('Net Project Cashflow', '$' . number_format($summary['net_project_cashflow'], 2))
                ->description('Revenue minus expenses')
                ->descriptionIcon($summary['net_project_cashflow'] >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($summary['net_project_cashflow'] >= 0 ? 'success' : 'danger'),

            Stat::make('Total Deposits', '$' . number_format($summary['total_deposits'], 2))
                ->description('All user deposits')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('info'),

            Stat::make('Total Withdrawals', '$' . number_format($summary['total_withdrawals'], 2))
                ->description('All user withdrawals')
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
