<?php

namespace App\Filament\Resources\CashflowResource\Widgets;

use App\Filament\Resources\CashflowResource;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MonthlyCashflowChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Cash in Hand Projection';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '800px';

    public ?string $filter = '6';

    protected function getData(): array
    {
        $months = (int) $this->filter;
        $monthlyData = CashflowResource::getMonthlyCashflowData($months, true);

        $currentBalance = CashflowResource::getCurrentCashBalance();

        $labels = array_column($monthlyData, 'month_label');
        $balances = array_column($monthlyData, 'running_balance');

        array_unshift($labels, 'Today');
        array_unshift($balances, $currentBalance);

        return [
            'datasets' => [
                [
                    'label' => 'Cash in Hand',
                    'data' => $balances,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
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
                'datalabels' => [
                    'display' => true,
                    'align' => 'top',
                    'color' => '#ffffff',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.9)',
                    'borderRadius' => 4,
                    'padding' => 6,
                    'font' => [
                        'weight' => 'bold',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => false,
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
