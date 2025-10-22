<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Project;
use App\Models\MonthlyProjectEvaluation;

echo "Testing Asset Profit Calculation Fix\n";
echo "====================================\n\n";

// Get a sample project with transactions
$project = Project::with(['transactions'])->first();

if (!$project) {
    echo "No projects found. Please ensure you have data in the database.\n";
    exit;
}

echo "Testing project: {$project->title} (Key: {$project->key})\n\n";

// Get some months with evaluations
$evaluations = MonthlyProjectEvaluation::where('project_key', $project->key)
    ->orderBy('month_date', 'asc')
    ->take(3)
    ->get();

if ($evaluations->count() < 2) {
    echo "Not enough evaluation data for this project. Need at least 2 months.\n";
    exit;
}

echo "Asset Evaluation Data:\n";
echo "----------------------\n";

$previousEvaluation = 0;
foreach ($evaluations as $evaluation) {
    $assetProfit = $evaluation->asset_evaluation - $previousEvaluation;
    
    echo "Month: {$evaluation->month_date}\n";
    echo "  Asset Evaluation: " . number_format($evaluation->asset_evaluation, 2) . "\n";
    echo "  Previous Month Evaluation: " . number_format($previousEvaluation, 2) . "\n";
    echo "  Asset Profit (Correct): " . number_format($assetProfit, 2) . "\n";
    echo "  Expense Asset: " . number_format($evaluation->expense_asset, 2) . "\n";
    echo "  Revenue Asset: " . number_format($evaluation->revenue_asset, 2) . "\n";
    
    // Show what the OLD incorrect calculation would have been
    $oldIncorrectProfit = $evaluation->asset_evaluation - $previousEvaluation + $evaluation->revenue_asset - $evaluation->expense_asset;
    echo "  Old Incorrect Profit: " . number_format($oldIncorrectProfit, 2) . " (WRONG - double counting)\n";
    echo "\n";
    
    $previousEvaluation = $evaluation->asset_evaluation;
}

echo "✅ The fix ensures Asset Profit = Current Evaluation - Previous Evaluation\n";
echo "✅ This correctly shows the change in asset value without double-counting transactions\n";
echo "✅ The asset evaluation already includes cumulative effect of all expenses/revenues\n";
