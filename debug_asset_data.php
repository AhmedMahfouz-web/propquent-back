<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Project;
use App\Models\MonthlyProjectEvaluation;
use App\Models\ProjectTransaction;

echo "Debugging Asset Revenue/Expense Data\n";
echo "====================================\n\n";

// Get a sample project
$project = Project::with(['transactions'])->first();

if (!$project) {
    echo "No projects found.\n";
    exit;
}

echo "Project: {$project->title} (Key: {$project->key})\n\n";

// Check if MonthlyProjectEvaluation has data for this project
$evaluations = MonthlyProjectEvaluation::where('project_key', $project->key)
    ->orderBy('month_date', 'asc')
    ->get();

echo "MonthlyProjectEvaluation Records: " . $evaluations->count() . "\n";

if ($evaluations->count() > 0) {
    echo "\nSample evaluation data:\n";
    foreach ($evaluations->take(3) as $eval) {
        echo "Month: {$eval->month_date}\n";
        echo "  Asset Evaluation: " . number_format($eval->asset_evaluation, 2) . "\n";
        echo "  Expense Asset: " . number_format($eval->expense_asset, 2) . "\n";
        echo "  Revenue Asset: " . number_format($eval->revenue_asset, 2) . "\n";
        echo "  Value Correction: " . number_format($eval->value_correction, 2) . "\n\n";
    }
} else {
    echo "❌ No MonthlyProjectEvaluation data found!\n";
    echo "You need to run: php artisan evaluations:calculate\n\n";
}

// Check raw project transactions
$assetTransactions = ProjectTransaction::where('project_key', $project->key)
    ->where('serving', 'asset')
    ->where('status', 'done')
    ->get();

echo "Raw Asset Transactions: " . $assetTransactions->count() . "\n";

if ($assetTransactions->count() > 0) {
    echo "\nSample asset transactions:\n";
    foreach ($assetTransactions->take(3) as $trans) {
        echo "Date: {$trans->transaction_date}\n";
        echo "  Type: {$trans->financial_type}\n";
        echo "  Amount: " . number_format($trans->amount, 2) . "\n";
        echo "  Status: {$trans->status}\n\n";
    }
} else {
    echo "❌ No asset transactions found!\n";
}
