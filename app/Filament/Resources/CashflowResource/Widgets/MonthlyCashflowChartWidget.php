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

        $currentBalance = CashflowResource::getCurrentCashBalance();

        $labels = array_column($weeklyData, 'week_label');
        $balances = array_column($weeklyData, 'running_balance');

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

    private function getWeeklyCashflowData(int $months): array
    {
        $startDate = now();
        $endDate = now()->addMonths($months);

        $runningBalance = CashflowResource::getCurrentCashBalance();

        $transactions = collect(DB::table('project_transactions')
            ->where('status', 'pending')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->selectRaw('due_date, financial_type, amount')
            ->orderBy('due_date')->get())
            ->merge(DB::table('user_transactions')
                ->where('status', 'pending')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->selectRaw('transaction_date as due_date, transaction_type as financial_type, amount')
                ->orderBy('transaction_date')->get());

        $weeklyData = [];
        $currentWeek = $startDate->copy()->startOfWeek();

        while ($currentWeek <= $endDate) {
            $weekEnd = $currentWeek->copy()->endOfWeek();

            $weekTransactions = $transactions->filter(function ($transaction) use ($currentWeek, $weekEnd) {
                return \Carbon\Carbon::parse($transaction->due_date)->between($currentWeek, $weekEnd);
            });

            $weeklyNet = $weekTransactions->reduce(function ($carry, $transaction) {
                $amount = (float) $transaction->amount;
                if (in_array($transaction->financial_type, ['revenue', 'deposit'])) {
                    return $carry + $amount;
                } else {
                    return $carry - $amount;
                }
            }, 0);

            $runningBalance += $weeklyNet;

            $weeklyData[] = [
                'week_label' => $currentWeek->format('M d'),
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
                'x' => [
                    'ticks' => [
                        'maxRotation' => 45,
                        'minRotation' => 45,
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
        ];
    }
}
