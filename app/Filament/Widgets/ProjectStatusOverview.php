<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProjectStatusOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalProjects = Project::count();
        $ongoingProjects = Project::where('status', 'on-going')->count();
        $exitedProjects = Project::where('status', 'exited')->count();
        $recentProjects = Project::where('created_at', '>=', now()->subDays(30))->count();

        // Calculate trends
        $lastMonthOngoing = Project::where('status', 'on-going')
            ->where('created_at', '<=', now()->subMonth())
            ->count();
        $ongoingTrend = $lastMonthOngoing > 0 ?
            (($ongoingProjects - $lastMonthOngoing) / $lastMonthOngoing) * 100 : 0;

        $lastMonthExited = Project::where('status', 'exited')
            ->where('updated_at', '<=', now()->subMonth())
            ->count();
        $exitedTrend = $lastMonthExited > 0 ?
            (($exitedProjects - $lastMonthExited) / $lastMonthExited) * 100 : 0;

        return [
            Stat::make('Total Projects', $totalProjects)
                ->description('All projects in system')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary')
                ->url('/admin/projects')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors',
                ]),

            Stat::make('Ongoing Projects', $ongoingProjects)
                ->description($ongoingTrend >= 0 ?
                    '+' . number_format($ongoingTrend, 1) . '% from last month' :
                    number_format($ongoingTrend, 1) . '% from last month')
                ->descriptionIcon($ongoingTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ongoingTrend >= 0 ? 'success' : 'danger')
                ->url('/admin/projects?tableFilters[status][value]=on-going')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors',
                ]),

            Stat::make('Exited Projects', $exitedProjects)
                ->description($exitedTrend >= 0 ?
                    '+' . number_format($exitedTrend, 1) . '% from last month' :
                    number_format($exitedTrend, 1) . '% from last month')
                ->descriptionIcon($exitedTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($exitedTrend >= 0 ? 'success' : 'warning')
                ->url('/admin/projects?tableFilters[status][value]=exited')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors',
                ]),

            Stat::make('New This Month', $recentProjects)
                ->description('Projects added in last 30 days')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('info')
                ->url('/admin/projects?tableFilters[created_at][value][from]=' . now()->subDays(30)->format('Y-m-d'))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors',
                ]),
        ];
    }

    public static function canView(): bool
    {
        return auth('admins')->check();
    }
}
