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

        // Create datasets for positive and negative segments with gray line
        $positiveData = [];
        $negativeData = [];

        foreach ($balances as $index => $balance) {
            if ($balance < 0) {
                $positiveData[] = null;
                $negativeData[] = $balance;
            } else {
                $positiveData[] = $balance;
                $negativeData[] = null;
            }
        }

        // Add connecting points where sign changes
        for ($i = 0; $i < count($balances) - 1; $i++) {
            $current = $balances[$i];
            $next = $balances[$i + 1];

            // If signs are different, add the next point to both datasets for connection
            if (($current < 0 && $next >= 0) || ($current >= 0 && $next < 0)) {
                if ($current < 0) {
                    $positiveData[$i + 1] = $next;
                } else {
                    $negativeData[$i + 1] = $next;
                }
            }
        }

        $datasets = [];

        // Add connecting line dataset first (behind the fill areas)
        $datasets[] = [
            'label' => 'Cash Balance Line',
            'data' => $balances,
            'backgroundColor' => 'transparent',
            'borderColor' => 'rgba(107, 114, 128, 1)',
            'pointBackgroundColor' => 'transparent',
            'pointBorderColor' => 'transparent',
            'borderWidth' => 2,
            'fill' => false,
            'tension' => 0.3,
            'pointRadius' => 0,
            'pointHoverRadius' => 0,
            'spanGaps' => true,
            'order' => 3,
        ];

        // Add positive dataset if it has data
        if (array_filter($positiveData, fn($val) => $val !== null)) {
            $datasets[] = [
                'label' => 'Cash in Hand (Positive)',
                'data' => $positiveData,
                'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                'borderColor' => 'transparent',
                'pointBackgroundColor' => 'rgba(34, 197, 94, 1)',
                'pointBorderColor' => 'rgba(34, 197, 94, 1)',
                'borderWidth' => 0,
                'fill' => 'origin',
                'tension' => 0.3,
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
                'spanGaps' => false,
                'order' => 2,
            ];
        }

        // Add negative dataset if it has data
        if (array_filter($negativeData, fn($val) => $val !== null)) {
            $datasets[] = [
                'label' => 'Cash in Hand (Negative)',
                'data' => $negativeData,
                'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                'borderColor' => 'transparent',
                'pointBackgroundColor' => 'rgba(239, 68, 68, 1)',
                'pointBorderColor' => 'rgba(239, 68, 68, 1)',
                'borderWidth' => 0,
                'fill' => 'origin',
                'tension' => 0.3,
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
                'spanGaps' => false,
                'order' => 1,
            ];
        }

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
