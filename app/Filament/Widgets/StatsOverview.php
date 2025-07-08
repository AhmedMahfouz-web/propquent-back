<?php

namespace App\Filament\Widgets;

use App\Models\Developer;
use App\Models\Project;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalProjects = Project::count();
        $ongoingProjects = Project::where('status', 'On-going')->count();
        $exitedProjects = Project::where('status', 'Exited')->count();

        return [
            Stat::make('Total Users', User::count())
                ->description('Total registered users')
                ->descriptionIcon('heroicon-m-user')
                ->color('primary'),
            Stat::make('Total Projects', $totalProjects)
                ->description("On-going: {$ongoingProjects} | Exited: {$exitedProjects}")
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('info'),
            Stat::make('Total Developers', Developer::count())
                ->description('Total developers listed')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('warning'),
        ];
    }
}
