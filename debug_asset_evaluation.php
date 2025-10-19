<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MonthlyProjectEvaluation;
use App\Models\Project;
use Carbon\Carbon;

echo "=== Asset Evaluation Debug Script ===\n\n";

// Get current month and a few months back
$currentMonth = Carbon::now()->format('Y-m-01');
$lastMonth = Carbon::now()->subMonth()->format('Y-m-01');
$twoMonthsAgo = Carbon::now()->subMonths(2)->format('Y-m-01');

$months = [$twoMonthsAgo, $lastMonth, $currentMonth];

echo "Checking months: " . implode(', ', $months) . "\n\n";

// Check if we have any evaluations
$totalEvaluations = MonthlyProjectEvaluation::count();
echo "Total evaluations in database: {$totalEvaluations}\n\n";

if ($totalEvaluations === 0) {
    echo "No evaluations found. Run 'php artisan evaluations:calculate' first.\n";
    exit;
}

// Check company totals for each month
echo "=== Company Asset Evaluation Totals ===\n";
foreach ($months as $month) {
    $companyTotal = MonthlyProjectEvaluation::getCompanyAssetEvaluation($month);
    $projectCount = MonthlyProjectEvaluation::where('month_date', $month)->count();
    
    echo "Month {$month}: {$companyTotal} USD (from {$projectCount} projects)\n";
}

echo "\n=== Individual Project Evaluations (Sample) ===\n";

// Get a sample project with evaluations
$sampleProject = MonthlyProjectEvaluation::with('project')
    ->whereIn('month_date', $months)
    ->first();

if ($sampleProject) {
    $projectKey = $sampleProject->project_key;
    echo "Sample Project: {$projectKey} - {$sampleProject->project->title}\n\n";
    
    $projectEvaluations = MonthlyProjectEvaluation::where('project_key', $projectKey)
        ->whereIn('month_date', $months)
        ->orderBy('month_date')
        ->get();
    
    echo "Month\t\tPrevious\tExpense\tRevenue\tCorrection\tFinal\n";
    echo str_repeat('-', 80) . "\n";
    
    foreach ($projectEvaluations as $eval) {
        printf(
            "%s\t%.2f\t\t%.2f\t%.2f\t%.2f\t\t%.2f\n",
            $eval->month_date->format('Y-m'),
            $eval->previous_evaluation,
            $eval->expense_asset,
            $eval->revenue_asset,
            $eval->value_correction,
            $eval->asset_evaluation
        );
        
        // Verify calculation
        $expected = $eval->previous_evaluation + $eval->expense_asset - $eval->revenue_asset + $eval->value_correction;
        if (abs($expected - $eval->asset_evaluation) > 0.01) {
            echo "  ⚠️  CALCULATION ERROR: Expected {$expected}, got {$eval->asset_evaluation}\n";
        }
    }
}

echo "\n=== Potential Issues Check ===\n";

// Check for duplicate records
$duplicates = MonthlyProjectEvaluation::select('project_key', 'month_date')
    ->groupBy('project_key', 'month_date')
    ->havingRaw('COUNT(*) > 1')
    ->get();

if ($duplicates->count() > 0) {
    echo "⚠️  Found {$duplicates->count()} duplicate project-month combinations!\n";
} else {
    echo "✅ No duplicate records found\n";
}

// Check for projects with no evaluations
$projectsWithTransactions = Project::has('transactions')->count();
$projectsWithEvaluations = MonthlyProjectEvaluation::distinct('project_key')->count('project_key');

echo "Projects with transactions: {$projectsWithTransactions}\n";
echo "Projects with evaluations: {$projectsWithEvaluations}\n";

if ($projectsWithTransactions > $projectsWithEvaluations) {
    echo "⚠️  Some projects with transactions don't have evaluations!\n";
} else {
    echo "✅ All projects with transactions have evaluations\n";
}

echo "\n=== Recent Evaluations ===\n";
$recentEvaluations = MonthlyProjectEvaluation::with('project')
    ->orderBy('updated_at', 'desc')
    ->take(5)
    ->get();

foreach ($recentEvaluations as $eval) {
    echo "Project: {$eval->project_key}, Month: {$eval->month_date->format('Y-m')}, Evaluation: {$eval->asset_evaluation}, Updated: {$eval->updated_at}\n";
}

echo "\nDebug script completed.\n";
