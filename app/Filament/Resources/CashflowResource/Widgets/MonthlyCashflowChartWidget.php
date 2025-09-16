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

        // Create point colors based on balance values
        $pointColors = [];
        
        foreach ($balances as $balance) {
            if ($balance < 0) {
                $pointColors[] = 'rgba(239, 68, 68, 1)';
            } else {
                $pointColors[] = 'rgba(34, 197, 94, 1)';
            }
        }

        // Determine overall color based on majority of values
        $negativeCount = count(array_filter($balances, fn($val) => $val < 0));
        $positiveCount = count($balances) - $negativeCount;
        
        if ($negativeCount > $positiveCount) {
            $mainColor = 'rgba(239, 68, 68, 1)';
            $fillColor = 'rgba(239, 68, 68, 0.2)';
        } else {
            $mainColor = 'rgba(34, 197, 94, 1)';
            $fillColor = 'rgba(34, 197, 94, 0.2)';
        }

        return [
            'datasets' => [
                [
                    'label' => 'Cash in Hand',
                    'data' => $balances,
                    'backgroundColor' => $fillColor,
                    'borderColor' => $mainColor,
                    'pointBackgroundColor' => $pointColors,
                    'pointBorderColor' => $pointColors,
                    'borderWidth' => 2,
                    'fill' => 'origin',
                    'tension' => 0.3,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
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
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
                'datalabels' => [
                    'display' => false, // Disable to prevent conflicts
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
                        'maxTicksLimit' => 50,
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'aspectRatio' => 0.5, // This makes the chart taller
            'layout' => [
                'padding' => 20
            ],
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
        ];
    }
}
