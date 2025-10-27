<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\UserTransaction;
use App\Models\MonthlyProjectEvaluation;
use App\Models\ProjectTransaction;
use Carbon\Carbon;

$userId = 41; // Ahmed Mahfouz
$currentDate = Carbon::parse('2025-10-27');
$septemberDate = Carbon::parse('2025-09-27');

echo "=== SEPTEMBER 2025 ===\n";
$sepMonth = $septemberDate->format('Y-m-01');
echo "Month: $sepMonth\n";

// User equity for September
$sepDeposits = UserTransaction::where('user_id', $userId)
    ->where('transaction_type', 'deposit')
    ->where('status', 'done')
    ->where('transaction_date', '<=', $septemberDate)
    ->sum('amount');
echo "User Deposits (up to Sep): $sepDeposits\n";

// Company total equity for September
$allDeposits = UserTransaction::where('transaction_type', 'deposit')
    ->where('status', 'done')
    ->where('transaction_date', '<=', $septemberDate)
    ->sum('amount');
    
$allWithdrawals = UserTransaction::where('transaction_type', 'withdrawal')
    ->where('status', 'done')
    ->where('transaction_date', '<=', $septemberDate)
    ->sum('amount');
    
$allRevenue = ProjectTransaction::where('financial_type', 'revenue')
    ->where('status', 'done')
    ->where('transaction_date', '<=', $septemberDate)
    ->sum('amount');
    
$allExpenses = ProjectTransaction::where('financial_type', 'expense')
    ->where('status', 'done')
    ->where('transaction_date', '<=', $septemberDate)
    ->sum('amount');

$sepCash = $allDeposits - $allWithdrawals + $allRevenue - $allExpenses;
$sepAssetEval = MonthlyProjectEvaluation::where('month_date', $sepMonth)->sum('asset_evaluation');
$sepCompanyEquity = $sepCash + $sepAssetEval;

echo "Company Cash (Sep): $sepCash\n";
echo "Company Asset Eval (Sep): $sepAssetEval\n";
echo "Company Total Equity (Sep): $sepCompanyEquity\n";

$sepEquityPercent = ($sepDeposits / $sepCompanyEquity) * 100;
echo "User Equity % (Sep): $sepEquityPercent%\n\n";

echo "=== OCTOBER 2025 ===\n";
$octMonth = $currentDate->format('Y-m-01');
echo "Month: $octMonth\n";

// User equity for October
$octDeposits = UserTransaction::where('user_id', $userId)
    ->where('transaction_type', 'deposit')
    ->where('status', 'done')
    ->where('transaction_date', '<=', $currentDate)
    ->sum('amount');
echo "User Deposits (up to Oct): $octDeposits\n";

// Company total equity for October
$allDeposits = UserTransaction::where('transaction_type', 'deposit')
    ->where('status', 'done')
    ->where('transaction_date', '<=', $currentDate)
    ->sum('amount');
    
$allWithdrawals = UserTransaction::where('transaction_type', 'withdrawal')
    ->where('status', 'done')
    ->where('transaction_date', '<=', $currentDate)
    ->sum('amount');
    
$allRevenue = ProjectTransaction::where('financial_type', 'revenue')
    ->where('status', 'done')
    ->where('transaction_date', '<=', $currentDate)
    ->sum('amount');
    
$allExpenses = ProjectTransaction::where('financial_type', 'expense')
    ->where('status', 'done')
    ->where('transaction_date', '<=', $currentDate)
    ->sum('amount');

$octCash = $allDeposits - $allWithdrawals + $allRevenue - $allExpenses;
$octAssetEval = MonthlyProjectEvaluation::where('month_date', $octMonth)->sum('asset_evaluation');
$octCompanyEquity = $octCash + $octAssetEval;

echo "Company Cash (Oct): $octCash\n";
echo "Company Asset Eval (Oct): $octAssetEval\n";
echo "Company Total Equity (Oct): $octCompanyEquity\n";

$octEquityPercent = ($octDeposits / $octCompanyEquity) * 100;
echo "User Equity % (Oct): $octEquityPercent%\n\n";

// Calculate October company profit
echo "=== OCTOBER COMPANY PROFIT ===\n";
$projects = \App\Models\Project::all();
$totalAssetProfit = 0;

foreach ($projects as $project) {
    $octEval = MonthlyProjectEvaluation::where('project_key', $project->key)
        ->where('month_date', $octMonth)
        ->first();
    
    $sepEval = MonthlyProjectEvaluation::where('project_key', $project->key)
        ->where('month_date', $sepMonth)
        ->first();
    
    if ($octEval) {
        $currentAssetEval = (float) $octEval->asset_evaluation;
        $previousAssetEval = $sepEval ? (float) $sepEval->asset_evaluation : 0;
        $revenueAsset = (float) $octEval->revenue_asset;
        $expenseAsset = (float) $octEval->expense_asset;
        
        $profitAsset = $currentAssetEval - $previousAssetEval + $revenueAsset - $expenseAsset;
        $totalAssetProfit += $profitAsset;
        
        echo "Project {$project->key}: Profit = $profitAsset (Eval: $previousAssetEval -> $currentAssetEval, Rev: $revenueAsset, Exp: $expenseAsset)\n";
    }
}

echo "\nTotal Asset Profit (Oct): $totalAssetProfit\n";

// Calculate user profit
echo "\n=== USER PROFIT CALCULATION ===\n";
$equityFraction = $sepEquityPercent / 100;
$userProfit = $equityFraction * $totalAssetProfit;

echo "September Equity %: $sepEquityPercent%\n";
echo "Equity Fraction: $equityFraction\n";
echo "October Company Profit: $totalAssetProfit\n";
echo "User Profit: $userProfit\n";
echo "\nExpected from User Financial Report: 1115.00\n";
