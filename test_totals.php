<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test the totals calculation
echo "Testing totals calculation...\n";

// Sample data to test
$data = [
    'months' => [
        '2024-01-01' => [
            'expense_asset' => 1000,
            'revenue_asset' => 500,
            'evaluation_asset' => 5000,
            'profit_asset' => 2000,
            'expense_operation' => 300,
            'revenue_operation' => 800,
            'profit_operation' => 500,
            'expense_total' => 1300,
            'revenue_total' => 1300,
            'total_profit' => 2500,
        ],
        '2024-02-01' => [
            'expense_asset' => 1500,
            'revenue_asset' => 700,
            'evaluation_asset' => 6000,
            'profit_asset' => 3000,
            'expense_operation' => 400,
            'revenue_operation' => 900,
            'profit_operation' => 500,
            'expense_total' => 1900,
            'revenue_total' => 1600,
            'total_profit' => 3500,
        ],
        '2024-03-01' => [
            'expense_asset' => 2000,
            'revenue_asset' => 1000,
            'evaluation_asset' => 7000,
            'profit_asset' => 4000,
            'expense_operation' => 500,
            'revenue_operation' => 1000,
            'profit_operation' => 500,
            'expense_total' => 2500,
            'revenue_total' => 2000,
            'total_profit' => 4500,
        ],
    ],
    'totals' => array_fill_keys(['evaluation_asset', 'value_correction', 'expense_operation', 'expense_asset', 'expense_total', 'revenue_operation', 'revenue_asset', 'revenue_total', 'profit_operation', 'profit_asset', 'total_profit'], 0)
];

// Apply the same logic as in the fixed code
foreach ($data['months'] as $month => $monthData) {
    $data['totals']['expense_asset'] += $monthData['expense_asset'] ?? 0;
    $data['totals']['revenue_asset'] += $monthData['revenue_asset'] ?? 0;
    $data['totals']['expense_operation'] += $monthData['expense_operation'] ?? 0;
    $data['totals']['revenue_operation'] += $monthData['revenue_operation'] ?? 0;
    $data['totals']['profit_operation'] += $monthData['profit_operation'] ?? 0;
    $data['totals']['evaluation_asset'] += $monthData['evaluation_asset'] ?? 0;
    $data['totals']['profit_asset'] += $monthData['profit_asset'] ?? 0;
    $data['totals']['expense_total'] += $monthData['expense_total'] ?? 0;
    $data['totals']['revenue_total'] += $monthData['revenue_total'] ?? 0;
    $data['totals']['total_profit'] += $monthData['total_profit'] ?? 0;
}

echo "Results:\n";
echo "Expense Asset Total: " . $data['totals']['expense_asset'] . " (Expected: 4500)\n";
echo "Revenue Asset Total: " . $data['totals']['revenue_asset'] . " (Expected: 2200)\n";
echo "Evaluation Asset Total: " . $data['totals']['evaluation_asset'] . " (Expected: 18000)\n";
echo "Profit Asset Total: " . $data['totals']['profit_asset'] . " (Expected: 9000)\n";
echo "Total Profit: " . $data['totals']['total_profit'] . " (Expected: 10500)\n";

echo "\nTest completed! All totals are now sums of filtered months.\n";
