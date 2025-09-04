<?php

namespace App\Filament\Resources\CashflowResource\Widgets;

use App\Filament\Resources\CashflowResource;
use Filament\Widgets\ChartWidget;

class MonthlyCashflowChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Monthly Cashflow Trend';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '900px';

    public ?string $filter = '12';

    protected function getData(): array
    {
        $months = (int) $this->filter;
        $monthlyData = CashflowResource::getMonthlyCashflowData($months);

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => array_column($monthlyData, 'revenue'),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                ],
                [
                    'label' => 'Expenses',
                    'data' => array_column($monthlyData, 'expenses'),
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                ],
                [
                    'label' => 'Deposits',
                    'data' => array_column($monthlyData, 'deposits'),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                ],
                [
                    'label' => 'Withdrawals',
                    'data' => array_column($monthlyData, 'withdrawals'),
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'borderColor' => 'rgba(245, 158, 11, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                ],
                [
                    'label' => 'Running Balance',
                    'data' => array_column($monthlyData, 'running_balance'),
                    'backgroundColor' => 'rgba(168, 85, 247, 0.3)',
                    'borderColor' => 'rgba(168, 85, 247, 1)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'type' => 'line',
                ],
            ],
            'labels' => array_column($monthlyData, 'month_label'),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            '6' => '6 Months',
            '12' => '12 Months',
            '24' => '24 Months',
            '36' => '36 Months',
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.dataset.label + ": $" + context.parsed.y.toLocaleString();
                        }'
                    ]
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) {
                            return "$" + value.toLocaleString();
                        }'
                    ],
                ],
                'x' => [
                    'display' => true,
                ],
            ],
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'elements' => [
                'point' => [
                    'radius' => 4,
                    'hoverRadius' => 6,
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'aspectRatio' => 0.8, // Makes chart much taller
        ];
    }
}
