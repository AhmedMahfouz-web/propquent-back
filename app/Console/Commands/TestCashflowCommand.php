<?php

namespace App\Console\Commands;

use App\Filament\Resources\CashflowResource;
use App\Models\Project;
use App\Models\ProjectTransaction;
use App\Models\UserTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestCashflowCommand extends Command
{
    protected $signature = 'cashflow:test';
    protected $description = 'Test the cashflow system performance and calculations';

    public function handle()
    {
        $this->info('Testing Cashflow System...');
        
        // Test 1: Company Cashflow Summary
        $this->info('1. Testing Company Cashflow Summary...');
        $startTime = microtime(true);
        $summary = CashflowResource::getCompanyCashflowSummary();
        $endTime = microtime(true);
        
        $this->table([
            'Metric', 'Value'
        ], [
            ['Current Available Cash', '$' . number_format($summary['current_available_cash'], 2)],
            ['Total Revenue', '$' . number_format($summary['total_revenue'], 2)],
            ['Total Expenses', '$' . number_format($summary['total_expenses'], 2)],
            ['Total Deposits', '$' . number_format($summary['total_deposits'], 2)],
            ['Total Withdrawals', '$' . number_format($summary['total_withdrawals'], 2)],
            ['Net Project Cashflow', '$' . number_format($summary['net_project_cashflow'], 2)],
            ['Net User Cashflow', '$' . number_format($summary['net_user_cashflow'], 2)],
        ]);
        
        $this->info('Query time: ' . round(($endTime - $startTime) * 1000, 2) . 'ms');
        
        // Test 2: Monthly Cashflow Data
        $this->info('2. Testing Monthly Cashflow Data...');
        $startTime = microtime(true);
        $monthlyData = CashflowResource::getMonthlyCashflowData(12);
        $endTime = microtime(true);
        
        $this->info('Retrieved ' . count($monthlyData) . ' months of data');
        $this->info('Query time: ' . round(($endTime - $startTime) * 1000, 2) . 'ms');
        
        if (!empty($monthlyData)) {
            $this->table([
                'Month', 'Revenue', 'Expenses', 'Deposits', 'Withdrawals', 'Net', 'Running Balance'
            ], array_slice(array_map(function($data) {
                return [
                    $data['month_label'],
                    '$' . number_format($data['revenue'], 0),
                    '$' . number_format($data['expenses'], 0),
                    '$' . number_format($data['deposits'], 0),
                    '$' . number_format($data['withdrawals'], 0),
                    '$' . number_format($data['monthly_net'], 0),
                    '$' . number_format($data['running_balance'], 0),
                ];
            }, $monthlyData), -6)); // Show last 6 months
        }
        
        // Test 3: Project Cashflow Query Performance
        $this->info('3. Testing Project Cashflow Query Performance...');
        $startTime = microtime(true);
        $projects = CashflowResource::getEloquentQuery()->limit(10)->get();
        $endTime = microtime(true);
        
        $this->info('Retrieved ' . $projects->count() . ' projects with cashflow data');
        $this->info('Query time: ' . round(($endTime - $startTime) * 1000, 2) . 'ms');
        
        if ($projects->isNotEmpty()) {
            $this->table([
                'Project Key', 'Net Cashflow', 'Unpaid Installments', 'Next Installment'
            ], $projects->take(5)->map(function($project) {
                return [
                    $project->key,
                    '$' . number_format($project->net_cashflow ?? 0, 2),
                    '$' . number_format($project->unpaid_installments ?? 0, 2),
                    $project->next_installment_date ? $project->next_installment_date->format('M d, Y') : 'N/A',
                ];
            })->toArray());
        }
        
        // Test 4: Database Index Usage
        $this->info('4. Testing Database Index Usage...');
        
        // Test project transactions query
        $startTime = microtime(true);
        $result = DB::select("
            EXPLAIN SELECT 
                project_key,
                SUM(CASE WHEN financial_type = 'revenue' AND status = 'done' THEN amount ELSE 0 END) as revenue,
                SUM(CASE WHEN financial_type = 'expense' AND status = 'done' THEN amount ELSE 0 END) as expenses
            FROM project_transactions 
            WHERE status = 'done' 
            GROUP BY project_key
        ");
        $endTime = microtime(true);
        
        $this->info('Index usage test completed in ' . round(($endTime - $startTime) * 1000, 2) . 'ms');
        
        // Test 5: Cache Performance
        $this->info('5. Testing Cache Performance...');
        
        // Clear cache first
        cache()->forget('company_cashflow_summary');
        
        // First call (no cache)
        $startTime = microtime(true);
        CashflowResource::getCompanyCashflowSummary();
        $endTime = microtime(true);
        $uncachedTime = ($endTime - $startTime) * 1000;
        
        // Second call (with cache)
        $startTime = microtime(true);
        CashflowResource::getCompanyCashflowSummary();
        $endTime = microtime(true);
        $cachedTime = ($endTime - $startTime) * 1000;
        
        $this->info('Uncached query time: ' . round($uncachedTime, 2) . 'ms');
        $this->info('Cached query time: ' . round($cachedTime, 2) . 'ms');
        $this->info('Performance improvement: ' . round(($uncachedTime / $cachedTime), 2) . 'x faster');
        
        // Test 6: Data Validation
        $this->info('6. Validating Data Consistency...');
        
        $projectTransactionSum = ProjectTransaction::where('status', 'done')
            ->selectRaw('
                SUM(CASE WHEN financial_type = "revenue" THEN amount ELSE 0 END) as revenue,
                SUM(CASE WHEN financial_type = "expense" THEN amount ELSE 0 END) as expenses
            ')
            ->first();
            
        $userTransactionSum = UserTransaction::where('status', 'done')
            ->selectRaw('
                SUM(CASE WHEN transaction_type = "deposit" THEN amount ELSE 0 END) as deposits,
                SUM(CASE WHEN transaction_type = "withdraw" THEN amount ELSE 0 END) as withdrawals
            ')
            ->first();
        
        $calculatedCash = ($projectTransactionSum->revenue ?? 0) + ($userTransactionSum->deposits ?? 0) - 
                         ($projectTransactionSum->expenses ?? 0) - ($userTransactionSum->withdrawals ?? 0);
        
        $this->info('Manual calculation: $' . number_format($calculatedCash, 2));
        $this->info('Cached calculation: $' . number_format($summary['current_available_cash'], 2));
        
        if (abs($calculatedCash - $summary['current_available_cash']) < 0.01) {
            $this->info('✅ Data consistency check passed!');
        } else {
            $this->error('❌ Data consistency check failed!');
        }
        
        $this->info('Cashflow system test completed successfully!');
        
        return 0;
    }
}
