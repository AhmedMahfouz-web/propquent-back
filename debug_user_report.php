<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\UserTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== User Financial Report Debug ===\n\n";

// Check if we have any user transactions
$totalTransactions = UserTransaction::count();
echo "Total UserTransactions: {$totalTransactions}\n";

if ($totalTransactions > 0) {
    // Check sample data
    $sample = UserTransaction::first();
    echo "Sample transaction:\n";
    echo "  Type: '{$sample->transaction_type}'\n";
    echo "  Status: '{$sample->status}'\n";
    echo "  Amount: {$sample->amount}\n";
    echo "  Date: {$sample->transaction_date}\n\n";
    
    // Check status distribution
    echo "Status distribution:\n";
    $statuses = UserTransaction::select('status', DB::raw('COUNT(*) as count'))
        ->groupBy('status')
        ->get();
    foreach ($statuses as $status) {
        echo "  '{$status->status}': {$status->count}\n";
    }
    
    // Check type distribution
    echo "\nType distribution:\n";
    $types = UserTransaction::select('transaction_type', DB::raw('COUNT(*) as count'))
        ->groupBy('transaction_type')
        ->get();
    foreach ($types as $type) {
        echo "  '{$type->transaction_type}': {$type->count}\n";
    }
    
    // Test the exact query from UserFinancialReport
    echo "\nTesting UserFinancialReport query:\n";
    $user = User::first();
    if ($user) {
        echo "Testing for user: {$user->full_name} (ID: {$user->id})\n";
        
        $query = UserTransaction::query()
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m-01') as month_date"),
                DB::raw("SUM(CASE WHEN transaction_type = 'deposit' THEN amount ELSE 0 END) as deposits"),
                DB::raw("SUM(CASE WHEN transaction_type = 'withdraw' THEN amount ELSE 0 END) as withdrawals"),
            )
            ->where('user_id', $user->id)
            ->where('status', 'done')
            ->groupBy('month_date');
            
        echo "SQL Query: " . $query->toSql() . "\n";
        echo "Bindings: " . json_encode($query->getBindings()) . "\n";
        
        $results = $query->get();
        echo "Results count: " . $results->count() . "\n";
        
        foreach ($results as $result) {
            echo "  Month: {$result->month_date}, Deposits: {$result->deposits}, Withdrawals: {$result->withdrawals}\n";
        }
        
        // Test without status filter
        echo "\nTesting without status filter:\n";
        $queryNoStatus = UserTransaction::query()
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m-01') as month_date"),
                DB::raw("SUM(CASE WHEN transaction_type = 'deposit' THEN amount ELSE 0 END) as deposits"),
                DB::raw("SUM(CASE WHEN transaction_type = 'withdraw' THEN amount ELSE 0 END) as withdrawals"),
            )
            ->where('user_id', $user->id)
            ->groupBy('month_date');
            
        $resultsNoStatus = $queryNoStatus->get();
        echo "Results without status filter: " . $resultsNoStatus->count() . "\n";
        
        foreach ($resultsNoStatus as $result) {
            echo "  Month: {$result->month_date}, Deposits: {$result->deposits}, Withdrawals: {$result->withdrawals}\n";
        }
    }
} else {
    echo "No user transactions found. Running seeder...\n";
    // Run the seeder
    $seeder = new \Database\Seeders\UserTransactionSeeder();
    $seeder->run();
    echo "Seeder completed. New count: " . UserTransaction::count() . "\n";
}
