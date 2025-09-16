<?php

namespace App\Filament\Resources\CashflowResource\Widgets;

use App\Filament\Resources\CashflowResource;
use Filament\Widgets\ChartWidget;

class MonthlyCashflowChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Cash in Hand Projection';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '900px';

    public ?string $filter = '6';

    protected function getData(): array
    {
        $months = (int) $this->filter;
        $monthlyData = CashflowResource::getMonthlyCashflowData($months, true); // Start from today

        return [
            'datasets' => [
                [
                    'label' => 'Cash in Hand',
                    'data' => array_column($monthlyData, 'running_balance'),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'type' => 'line',
                    'tension' => 0.4,
                    'pointBackgroundColor' => 'rgba(34, 197, 94, 1)',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 6,
                    'pointHoverRadius' => 8,
                    'datalabels' => [
                        'display' => true,
                        'align' => 'top',
                        'anchor' => 'end',
                        'backgroundColor' => 'rgba(34, 197, 94, 0.9)',
                        'borderColor' => 'rgba(34, 197, 94, 1)',
                        'borderRadius' => 4,
                        'borderWidth' => 1,
                        'color' => '#ffffff',
                        'font' => [
                            'size' => 10,
                            'weight' => 'bold'
                        ],
                        'padding' => [
                            'top' => 2,
                            'bottom' => 2,
                            'left' => 4,
                            'right' => 4
                        ],
                        'formatter' => 'function(value, context) {
                            if (value === null || value === undefined) return "";
                            const formatted = "$" + Math.round(value).toLocaleString();
                            return formatted;
                        }',
                        'clip' => false
                    ],
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
            '3' => '3 Months',
            '6' => '6 Months',
            '12' => '12 Months',
            '24' => '24 Months',
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
                    'beginAtZero' => false,
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
