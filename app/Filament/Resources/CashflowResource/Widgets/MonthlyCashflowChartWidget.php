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

    protected static ?string $maxHeight = '1200px';

    public ?string $filter = '6';

    protected function getData(): array
    {
        $months = (int) $this->filter;
        $weeklyData = $this->getWeeklyCashflowData($months);

        // Get current cash balance to show as starting point
        $currentBalance = CashflowResource::getCurrentCashBalance();

        // Add current week as starting point
        $labels = array_column($weeklyData, 'week_label');
        $balances = array_column($weeklyData, 'running_balance');

        // Prepend current week with current balance
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
                    'type' => 'line',
                    'tension' => 0.4,
                    'pointBackgroundColor' => 'rgba(34, 197, 94, 1)',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 6,
                    'pointHoverRadius' => 8,
                ],
            ],
            'labels' => $labels,
        ];
    }

    private function getWeeklyCashflowData(int $months): array
    {
        $startDate = now();
        $endDate = now()->addMonths($months);

        // Get current cash balance as starting point
        $runningBalance = CashflowResource::getCurrentCashBalance();

        // Get pending transactions for future projections
        $transactions = collect(DB::table('project_transactions')
            ->where('status', 'pending')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->selectRaw('
                due_date,
                financial_type,
                amount
            ')
            ->orderBy('due_date')
            ->get())
            ->merge(DB::table('user_transactions')
                ->where('status', 'pending')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->selectRaw('
                    transaction_date as due_date,
                    transaction_type as financial_type,
                    amount
                ')
                ->orderBy('transaction_date')
                ->get());

        // Generate weekly periods
        $weeklyData = [];
        $currentWeek = $startDate->copy()->startOfWeek();

        while ($currentWeek <= $endDate) {
            $weekEnd = $currentWeek->copy()->endOfWeek();

            // Get transactions for this week
            $weekTransactions = $transactions->filter(function ($transaction) use ($currentWeek, $weekEnd) {
                $transactionDate = \Carbon\Carbon::parse($transaction->due_date);
                return $transactionDate->between($currentWeek, $weekEnd);
            });

            // Calculate weekly net change
            $weeklyNet = 0;
            foreach ($weekTransactions as $transaction) {
                if (in_array($transaction->financial_type, ['revenue', 'deposit'])) {
                    $weeklyNet += $transaction->amount;
                } else {
                    $weeklyNet -= $transaction->amount;
                }
            }

            $runningBalance += $weeklyNet;

            $weeklyData[] = [
                'week_start' => $currentWeek->format('Y-m-d'),
                'week_label' => $currentWeek->format('M d') . ' - ' . $weekEnd->format('M d'),
                'weekly_net' => (float) $weeklyNet,
                'running_balance' => (float) $runningBalance,
            ];

            $currentWeek->addWeek();
        }

        return $weeklyData;
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
                    'enabled' => true,
                ],
                'datalabels' => [
                    'display' => true,
                    'align' => 'top',
                    'anchor' => 'end',
                    'offset' => 10,
                    'color' => '#ffffff',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.95)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderRadius' => 6,
                    'borderWidth' => 1,
                    'padding' => 6,
                    'font' => [
                        'weight' => 'bold',
                        'size' => 10
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => false,
                ],
                'x' => [
                    'display' => true,
                    'ticks' => [
                        'maxRotation' => 0,
                        'minRotation' => 0,
                        'maxTicksLimit' => 20
                    ]
                ],
            ],
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'elements' => [
                'point' => [
                    'radius' => 5,
                    'hoverRadius' => 7,
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'layout' => [
                'padding' => [
                    'top' => 30,
                    'bottom' => 10,
                    'left' => 10,
                    'right' => 10
                ]
            ]
        ];
    }
}
