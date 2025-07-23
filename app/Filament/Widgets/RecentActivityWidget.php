<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\ProjectTransaction;
use App\Models\SystemConfiguration;
use Filament\Widgets\Widget;
use Carbon\Carbon;

class RecentActivityWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-activity';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    public function getRecentActivities(): array
    {
        $activities = collect();

        // Recent project status changes (last 30 days)
        $recentProjects = Project::where('updated_at', '>=', now()->subDays(30))
            ->with('developer')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($recentProjects as $project) {
            $activities->push([
                'type' => 'project_status',
                'title' => 'Project Status Updated',
                'description' => "Project '{$project->name}' status changed to {$project->status}",
                'project' => $project->name,
                'developer' => $project->developer?->name ?? 'Unknown',
                'status' => $project->status,
                'timestamp' => $project->updated_at,
                'url' => "/admin/projects/{$project->id}",
                'icon' => 'heroicon-o-building-office-2',
                'color' => $this->getStatusColor($project->status),
            ]);
        }

        // Recent transactions (last 30 days)
        $recentTransactions = ProjectTransaction::where('created_at', '>=', now()->subDays(30))
            ->with('project.developer')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        foreach ($recentTransactions as $transaction) {
            $activities->push([
                'type' => 'transaction',
                'title' => 'New Transaction',
                'description' => "Transaction of $" . number_format($transaction->amount, 2) . " ({$transaction->type})",
                'project' => $transaction->project?->name ?? 'Unknown Project',
                'developer' => $transaction->project?->developer?->name ?? 'Unknown',
                'amount' => $transaction->amount,
                'transaction_type' => $transaction->type,
                'timestamp' => $transaction->created_at,
                'url' => "/admin/project-transactions/{$transaction->id}",
                'icon' => 'heroicon-o-banknotes',
                'color' => $this->getTransactionColor($transaction->type),
            ]);
        }

        // Recent configuration changes (last 7 days)
        $recentConfigs = SystemConfiguration::where('updated_at', '>=', now()->subDays(7))
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentConfigs as $config) {
            $activities->push([
                'type' => 'configuration',
                'title' => 'Configuration Updated',
                'description' => "System configuration '{$config->label}' was updated",
                'category' => $config->category,
                'config_key' => $config->key,
                'timestamp' => $config->updated_at,
                'url' => '/admin/system-settings',
                'icon' => 'heroicon-o-cog-6-tooth',
                'color' => 'text-blue-600',
            ]);
        }

        // Sort all activities by timestamp and limit to 20
        return $activities->sortByDesc('timestamp')->take(20)->values()->toArray();
    }

    public function getActivityStats(): array
    {
        $last24Hours = now()->subDay();
        $last7Days = now()->subDays(7);

        return [
            'projects_updated_24h' => Project::where('updated_at', '>=', $last24Hours)->count(),
            'transactions_24h' => ProjectTransaction::where('created_at', '>=', $last24Hours)->count(),
            'total_transactions_7d' => ProjectTransaction::where('created_at', '>=', $last7Days)->sum('amount'),
            'config_changes_7d' => SystemConfiguration::where('updated_at', '>=', $last7Days)->count(),
        ];
    }

    private function getStatusColor(string $status): string
    {
        return match ($status) {
            'on-going' => 'text-green-600',
            'exited' => 'text-red-600',
            'planning' => 'text-blue-600',
            'construction' => 'text-orange-600',
            'completed' => 'text-purple-600',
            'cancelled' => 'text-gray-600',
            'paused' => 'text-yellow-600',
            'sold' => 'text-emerald-600',
            default => 'text-gray-600',
        };
    }

    private function getTransactionColor(?string $type): string
    {
        if ($type === null) {
            return 'text-gray-600';
        }

        return match ($type) {
            'investment' => 'text-blue-600',
            'revenue' => 'text-green-600',
            'expense' => 'text-red-600',
            'profit' => 'text-purple-600',
            'sale' => 'text-emerald-600',
            'purchase' => 'text-orange-600',
            default => 'text-gray-600',
        };
    }

    public function getActivityIcon(string $type): string
    {
        return match ($type) {
            'project_status' => 'heroicon-o-building-office-2',
            'transaction' => 'heroicon-o-banknotes',
            'configuration' => 'heroicon-o-cog-6-tooth',
            default => 'heroicon-o-bell',
        };
    }

    public function formatTimeAgo(Carbon $timestamp): string
    {
        return $timestamp->diffForHumans();
    }

    public static function canView(): bool
    {
        return auth('admins')->check();
    }
}
