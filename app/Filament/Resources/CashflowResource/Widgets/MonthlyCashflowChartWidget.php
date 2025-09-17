<?php

namespace App\Filament\Resources\CashflowResource\Widgets;

use App\Filament\Resources\CashflowResource;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MonthlyCashflowChartWidget extends LineChartWidget
{
    protected static ?string $heading = 'Monthly Cashflow Breakdown';

    protected static ?int $sort = 2;

    protected $minValue;
    protected $maxValue;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '2000px';

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

        // Create single connected gray line dataset
        $datasets = [
            [
                'label' => 'Cash Balance',
                'data' => $balances,
                'backgroundColor' => 'rgba(107, 114, 128, 0.1)',
                'borderColor' => 'rgba(107, 114, 128, 1)',
                'pointBackgroundColor' => 'rgba(107, 114, 128, 1)',
                'pointBorderColor' => 'rgba(107, 114, 128, 1)',
                'borderWidth' => 2,
                'fill' => false,
                'tension' => 0.3,
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
                'spanGaps' => true,
            ]
        ];

        // Store min/max values for Y-axis configuration
        $this->minValue = min($balances);
        $this->maxValue = max($balances);
        
        return [
            'datasets' => $datasets,
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
                'week_label' => $currentWeek->format('M d') . ' (W' . (ceil($currentWeek->day / 7)) . ')',
                'running_balance' => (float) $runningBalance,
                'weekly_net' => (float) $weeklyNet,
                'week_number' => ceil($currentWeek->day / 7),
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
            'aspectRatio' => 0.5,
            'responsive' => true,
            'maintainAspectRatio' => false,
            'layout' => [
                'padding' => 20
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'display' => true,
                    'beginAtZero' => true,
                ],
                'x' => [
                    'display' => true,
                ],
            ],
            'elements' => [
                'point' => [
                    'hoverRadius' => 8,
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }
}
