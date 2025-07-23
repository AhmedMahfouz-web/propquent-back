<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\ProjectTransaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TrendAnalysisWidget extends ChartWidget
{
    protected static ?string $heading = 'Trend Analysis';

    protected static ?int $sort = 5;

    protected static ?string $maxHeight = '400px';

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '120s';

    public ?string $filter = 'projects_vs_transactions';

    protected function getData(): array
    {
        return match ($this->filter) {
            'revenue_vs_investment' => $this->getRevenueVsInvestmentData(),
            'project_lifecycle' => $this->getProjectLifecycleData(),
            'monthly_performance' => $this->getMonthlyPerformanceData(),
            default => $this->getProjectsVsTransactionsData(),
        };
    }

    protected function getType(): string
    {
        return match ($this->filter) {
            'project_lifecycle' => 'bar',
            'monthly_performance' => 'line',
            default => 'line',
        };
    }

    protected function getOptions(): array
    {
        $baseOptions = [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                        'font' => [
                            'size' => 12,
                        ],
                    ],
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
        ];

        // Customize options based on filter
        return match ($this->filter) {
            'revenue_vs_investment' => array_merge($baseOptions, [
                'plugins' => array_merge($baseOptions['plugins'], [
                    'tooltip' => [
                        'callbacks' => [
                            'label' => 'function(context) {
                                return context.dataset.label + ": $" + context.parsed.y.toLocaleString();
                            }',
                        ],
                    ],
                ]),
                'scales' => array_merge($baseOptions['scales'], [
                    'y' => array_merge($baseOptions['scales']['y'], [
                        'ticks' => [
                            'callback' => 'function(value) {
                                return "$" + value.toLocaleString();
                            }',
                        ],
                    ]),
                ]),
            ]),
            'monthly_performance' => array_merge($baseOptions, [
                'plugins' => array_merge($baseOptions['plugins'], [
                    'tooltip' => [
                        'callbacks' => [
                            'label' => 'function(context) {
                                if (context.dataset.label.includes("ROI")) {
                                    return context.dataset.label + ": " + context.parsed.y.toFixed(1) + "%";
                                }
                                return context.dataset.label + ": $" + context.parsed.y.toLocaleString();
                            }',
                        ],
                    ],
                ]),
                'scales' => array_merge($baseOptions['scales'], [
                    'y' => array_merge($baseOptions['scales']['y'], [
                        'type' => 'linear',
                        'display' => true,
                        'position' => 'left',
                    ]),
                    'y1' => [
                        'type' => 'linear',
                        'display' => true,
                        'position' => 'right',
                        'grid' => [
                            'drawOnChartArea' => false,
                        ],
                        'ticks' => [
                            'callback' => 'function(value) {
                                return value + "%";
                            }',
                        ],
                    ],
                ]),
            ]),
            default => $baseOptions,
        };
    }

    protected function getFilters(): ?array
    {
        return [
            'projects_vs_transactions' => 'Projects vs Transactions',
            'revenue_vs_investment' => 'Revenue vs Investment',
            'project_lifecycle' => 'Project Lifecycle',
            'monthly_performance' => 'Monthly Performance',
        ];
    }

    private function getProjectsVsTransactionsData(): array
    {
        $months = $this->getLast12Months();

        // Bulk query for better performance
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $projectsByMonth = Project::selectRaw('DATE_FORMAT(created_at, "%b %Y") as month, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $transactionsByMonth = ProjectTransaction::selectRaw('DATE_FORMAT(created_at, "%b %Y") as month, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $projectData = [];
        $transactionData = [];

        foreach ($months as $month) {
            $projectData[] = $projectsByMonth[$month] ?? 0;
            $transactionData[] = $transactionsByMonth[$month] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Projects',
                    'data' => $projectData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => '#3b82f6',
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'New Transactions',
                    'data' => $transactionData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => '#10b981',
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $months,
        ];
    }

    private function getRevenueVsInvestmentData(): array
    {
        $months = $this->getLast12Months();

        // Bulk query for better performance
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $revenueByMonth = ProjectTransaction::selectRaw('DATE_FORMAT(created_at, "%b %Y") as month, SUM(amount) as total')
            ->where('type', 'revenue')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $investmentByMonth = ProjectTransaction::selectRaw('DATE_FORMAT(created_at, "%b %Y") as month, SUM(amount) as total')
            ->where('type', 'investment')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $revenueData = [];
        $investmentData = [];

        foreach ($months as $month) {
            $revenueData[] = $revenueByMonth[$month] ?? 0;
            $investmentData[] = $investmentByMonth[$month] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Revenue',
                    'data' => $revenueData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => '#10b981',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Total Investment',
                    'data' => $investmentData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => '#3b82f6',
                    'yAxisID' => 'y',
                ],
            ],
            'labels' => $months,
        ];
    }

    private function getProjectLifecycleData(): array
    {
        $statusData = Project::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $labels = $statusData->pluck('status')->map(fn($s) => Str::title(str_replace('_', ' ', $s)));
        $data = $statusData->pluck('count');
        $colors = $statusData->pluck('status')->map(fn($s) => $this->getStatusColor($s));

        return [
            'datasets' => [
                [
                    'label' => 'Project Count',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    private function getMonthlyPerformanceData(): array
    {
        $months = $this->getLast12Months();

        // Bulk query for better performance
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $transactionsByMonth = ProjectTransaction::selectRaw('
                DATE_FORMAT(created_at, "%b %Y") as month,
                type,
                SUM(amount) as total
            ')
            ->whereIn('type', ['revenue', 'investment', 'expense'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month', 'type')
            ->get()
            ->groupBy('month');

        $netProfitData = [];
        $roiData = [];

        foreach ($months as $month) {
            $monthData = $transactionsByMonth->get($month, collect());

            $revenue = $monthData->where('type', 'revenue')->sum('total');
            $investment = $monthData->where('type', 'investment')->sum('total');
            $expenses = $monthData->where('type', 'expense')->sum('total');

            $netProfit = $revenue - $expenses;
            $totalInvestment = $investment ?: 1; // Avoid division by zero
            $roi = ($netProfit / $totalInvestment) * 100;

            $netProfitData[] = $netProfit;
            $roiData[] = round($roi, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Net Profit',
                    'data' => $netProfitData,
                    'borderColor' => '#8b5cf6',
                    'backgroundColor' => '#8b5cf6',
                    'yAxisID' => 'y',
                    'type' => 'bar',
                ],
                [
                    'label' => 'ROI (%)',
                    'data' => $roiData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => '#f59e0b',
                    'yAxisID' => 'y1',
                    'type' => 'line',
                    'fill' => false,
                ],
            ],
            'labels' => $months,
        ];
    }

    private function getStatusColor(string $status): string
    {
        return match ($status) {
            'on-going' => '#22c55e',
            'exited' => '#ef4444',
            'planning' => '#3b82f6',
            'construction' => '#f97316',
            'completed' => '#8b5cf6',
            'cancelled' => '#6b7280',
            'paused' => '#eab308',
            'sold' => '#10b981',
            default => '#9ca3af',
        };
    }

    private function getLast12Months(): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $months[] = Carbon::now()->subMonths($i)->format('M Y');
        }
        return $months;
    }
}
