<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Services\ConfigurationService;
use Filament\Widgets\ChartWidget;

class ProjectStatusWidget extends ChartWidget
{
    protected static ?string $heading = 'Project Status Distribution';

    protected static ?int $sort = 1;

    protected static ?string $maxHeight = '300px';

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $configService = new ConfigurationService();
        $statusOptions = $configService->getOptions('project_statuses');

        $statusCounts = Project::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $labels = [];
        $data = [];
        $colors = [];
        $totalProjects = array_sum($statusCounts);

        // Define colors for different statuses
        $statusColors = [
            'on-going' => '#10b981', // Green
            'exited' => '#ef4444',   // Red
            'planning' => '#3b82f6', // Blue
            'construction' => '#f59e0b', // Orange
            'completed' => '#8b5cf6', // Purple
            'cancelled' => '#6b7280', // Gray
            'paused' => '#f97316',   // Orange-red
            'sold' => '#059669',     // Emerald
        ];

        foreach ($statusOptions as $statusKey => $statusLabel) {
            $count = $statusCounts[$statusKey] ?? 0;
            if ($count > 0 || in_array($statusKey, ['on-going', 'exited'])) {
                $labels[] = $statusLabel;
                $data[] = $count;
                $colors[] = $statusColors[$statusKey] ?? '#6b7280';
            }
        }

        // Add any statuses not in configuration but exist in data
        foreach ($statusCounts as $status => $count) {
            if (!array_key_exists($status, $statusOptions)) {
                $labels[] = ucwords(str_replace('_', ' ', $status));
                $data[] = $count;
                $colors[] = '#6b7280';
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Projects',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => array_map(fn($color) => $color, $colors),
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
            'totalProjects' => $totalProjects,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                        'font' => [
                            'size' => 12,
                        ],
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const label = context.label || "";
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ": " + value + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
            'cutout' => '60%',
            'onClick' => 'function(event, elements) {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    const label = this.data.labels[index];
                    window.dispatchEvent(new CustomEvent("project-status-clicked", {
                        detail: { status: label, index: index }
                    }));
                }
            }',
        ];
    }

    public function getDescription(): ?string
    {
        $data = $this->getData();
        $total = $data['totalProjects'] ?? 0;

        if ($total === 0) {
            return 'No projects found in the system.';
        }

        return "Total of {$total} projects across all statuses. Click on a segment to view details.";
    }

    protected function getFilters(): ?array
    {
        $configService = new ConfigurationService();
        $developers = \App\Models\Developer::where('status', 'active')->pluck('name', 'id')->toArray();

        return [
            'all' => 'All Projects',
            'developer' => 'By Developer',
            'recent' => 'Recent (Last 30 days)',
            'active' => 'Active Only',
        ];
    }

    public function getFilteredData(string $filter): array
    {
        $query = Project::query();

        switch ($filter) {
            case 'recent':
                $query->where('created_at', '>=', now()->subDays(30));
                break;
            case 'active':
                $query->whereIn('status', ['on-going', 'planning', 'construction']);
                break;
            case 'developer':
                // This would be handled by a separate filter dropdown
                break;
            default:
                // 'all' - no additional filtering
                break;
        }

        $statusCounts = $query->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return $this->formatChartData($statusCounts);
    }

    private function formatChartData(array $statusCounts): array
    {
        $configService = new ConfigurationService();
        $statusOptions = $configService->getOptions('project_statuses');

        $labels = [];
        $data = [];
        $colors = [];

        $statusColors = [
            'on-going' => '#10b981',
            'exited' => '#ef4444',
            'planning' => '#3b82f6',
            'construction' => '#f59e0b',
            'completed' => '#8b5cf6',
            'cancelled' => '#6b7280',
            'paused' => '#f97316',
            'sold' => '#059669',
        ];

        foreach ($statusOptions as $statusKey => $statusLabel) {
            $count = $statusCounts[$statusKey] ?? 0;
            if ($count > 0) {
                $labels[] = $statusLabel;
                $data[] = $count;
                $colors[] = $statusColors[$statusKey] ?? '#6b7280';
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Projects',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
            'totalProjects' => array_sum($data),
        ];
    }

    public static function canView(): bool
    {
        return auth('admins')->check();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->size('sm')
                ->action(function () {
                    $this->dispatch('$refresh');
                }),

            \Filament\Actions\Action::make('viewDetails')
                ->label('View Details')
                ->icon('heroicon-o-eye')
                ->color('primary')
                ->size('sm')
                ->url('/admin/projects')
                ->openUrlInNewTab(false),
        ];
    }
}
