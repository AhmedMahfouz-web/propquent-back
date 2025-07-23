<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Project;
use App\Models\ProfitDistribution;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Active Investors', User::count())
                ->description('Total users in the system')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Total Projects', Project::count())
                ->description('Total projects in the system')
                ->descriptionIcon('heroicon-m-chart-bar-square')
                ->color('info'),

        ];
    }
}
