<?php

namespace App\Filament\Widgets;

use App\Models\ProjectTransaction;
use App\Services\ConfigurationService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionSummaryWidget extends ChartWidget
{
    protected static ?string $heading = 'Transaction Summary by Type';

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '300px';

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    public ?string $filter = 'monthly';

    protected function getData(): array
    {
        $configService = new ConfigurationService();
        $transactionTypes = $configService->getOptions('transaction_types');
        $period = $this->getPeriod();
        $labels = $this->getLabels();

        $startRange = $this->getLabelStartDate($labels[0]);
        $endRange = $this->getLabelEndDate(end($labels));

        $dateFormat = match ($period['unit']) {
            'week' => '%Y-%u',
            'quarter' => '%Y-Q%q',
            default => '%Y-%m',
        };

        $results = ProjectTransaction::query()
            ->select(
                'financial_type',
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                DB::raw('SUM(amount) as total')
            )
            ->whereIn('financial_type', array_keys($transactionTypes))
            ->whereBetween('created_at', [$startRange, $endRange])
            ->groupBy('financial_type', 'period')
            ->get()
            ->groupBy('financial_type');

        $datasets = [];
        foreach ($transactionTypes as $typeKey => $typeLabel) {
            $dataForType = $results->get($typeKey, collect())->keyBy('period');
            $data = [];

            foreach ($labels as $label) {
                $periodKey = match ($period['unit']) {
                    'week' => Carbon::parse($label)->format('Y-W'),
                    'quarter' => str_replace(' ', '-', $label),
                    default => Carbon::createFromFormat($period['format'], $label)->format('Y-m'),
                };
                $data[] = $dataForType->get($periodKey)->total ?? 0;
            }

            $backgroundColor = $this->getTypeColor($typeKey);
            $datasets[] = [
                'label' => $typeLabel,
                'data' => $data,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $backgroundColor,
                'borderWidth' => 2,
                'fill' => false,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 15,
                    ],
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.dataset.label + ": $" + context.parsed.y.toLocaleString();
                        }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) {
                            return "$" + value.toLocaleString();
                        }',
                    ],
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'weekly' => 'Last 8 Weeks',
            'monthly' => 'Last 12 Months',
            'quarterly' => 'Last 8 Quarters',
        ];
    }

    private function getPeriod(): array
    {
        return match ($this->filter) {
            'weekly' => [
                'count' => 8,
                'unit' => 'week',
                'format' => 'M d',
            ],
            'quarterly' => [
                'count' => 8,
                'unit' => 'quarter',
                'format' => 'Y Q',
            ],
            default => [
                'count' => 12,
                'unit' => 'month',
                'format' => 'M Y',
            ],
        };
    }

    private function getLabels(): array
    {
        $period = $this->getPeriod();
        $labels = [];

        for ($i = $period['count'] - 1; $i >= 0; $i--) {
            $date = match ($period['unit']) {
                'week' => Carbon::now()->subWeeks($i)->startOfWeek(),
                'quarter' => Carbon::now()->subQuarters($i)->startOfQuarter(),
                default => Carbon::now()->subMonths($i)->startOfMonth(),
            };

            $labels[] = match ($period['unit']) {
                'quarter' => $date->year . ' Q' . $date->quarter,
                default => $date->format($period['format']),
            };
        }

        return $labels;
    }

    private function getLabelStartDate(string $label): Carbon
    {
        $period = $this->getPeriod();

        return match ($period['unit']) {
            'week' => Carbon::parse($label)->startOfWeek(),
            'quarter' => Carbon::createFromFormat('Y Q', $label)->startOfQuarter(),
            default => Carbon::createFromFormat($period['format'], $label)->startOfMonth(),
        };
    }

    private function getLabelEndDate(string $label): Carbon
    {
        $period = $this->getPeriod();

        return match ($period['unit']) {
            'week' => Carbon::parse($label)->endOfWeek(),
            'quarter' => Carbon::createFromFormat('Y Q', $label)->endOfQuarter(),
            default => Carbon::createFromFormat($period['format'], $label)->endOfMonth(),
        };
    }

    private function getTypeColor(string $type): string
    {
        $colors = [
            'investment' => '#3b82f6',    // Blue
            'revenue' => '#10b981',       // Green
            'expense' => '#ef4444',       // Red
            'profit' => '#8b5cf6',        // Purple
            'sale' => '#059669',          // Emerald
            'purchase' => '#f59e0b',      // Orange
            'maintenance' => '#6b7280',   // Gray
            'tax' => '#dc2626',           // Red-600
            'fee' => '#7c3aed',           // Violet
            'dividend' => '#0891b2',      // Cyan
        ];

        return $colors[$type] ?? '#6b7280';
    }

    public function getDescription(): ?string
    {
        $period = $this->getPeriod();
        $periodText = match ($this->filter) {
            'weekly' => 'last 8 weeks',
            'quarterly' => 'last 8 quarters',
            default => 'last 12 months',
        };

        return "Transaction amounts by type over the {$periodText}. Click legend items to toggle visibility.";
    }

    public static function canView(): bool
    {
        return auth('admins')->check();
    }
}
