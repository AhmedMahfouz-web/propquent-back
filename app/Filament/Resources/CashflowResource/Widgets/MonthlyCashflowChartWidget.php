<?php

namespace App\Filament\Resources\CashflowResource\Widgets;

use App\Filament\Resources\CashflowResource;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Log;

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
        $monthlyData = CashflowResource::getMonthlyCashflowData($months, false); // Include historical data for better variation

        // Debug: Log the data to see what's happening
        Log::info('Chart Data for ' . $months . ' months:', [
            'count' => count($monthlyData),
            'data' => array_map(function($item) {
                return [
                    'month' => $item['month_label'],
                    'balance' => $item['running_balance'],
                    'net' => $item['monthly_net']
                ];
            }, $monthlyData)
        ]);

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
                'datalabels' => [
                    'display' => true,
                    'align' => 'top',
                    'anchor' => 'end',
                    'formatter' => 'function(value) {
                        return "$" + Math.round(value).toLocaleString();
                    }',
                    'font' => [
                        'weight' => 'bold',
                        'size' => 11
                    ],
                    'color' => '#ffffff',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.9)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderRadius' => 4,
                    'borderWidth' => 1,
                    'padding' => 4,
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
