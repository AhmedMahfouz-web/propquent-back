<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\UserTransaction;
use Illuminate\Support\Facades\DB;

echo "=== UserTransaction Debug ===\n";

// Check constants
echo "Constants:\n";
echo "TYPE_DEPOSIT: '" . UserTransaction::TYPE_DEPOSIT . "'\n";
echo "TYPE_WITHDRAWAL: '" . UserTransaction::TYPE_WITHDRAWAL . "'\n";
echo "STATUS_DONE: '" . UserTransaction::STATUS_DONE . "'\n\n";

// Check raw data
echo "Raw transaction types in database:\n";
$types = DB::table('user_transactions')
    ->select('transaction_type', DB::raw('COUNT(*) as count'))
    ->groupBy('transaction_type')
    ->get();

foreach ($types as $type) {
    echo "'{$type->transaction_type}': {$type->count} records\n";
}

echo "\nRaw status values in database:\n";
$statuses = DB::table('user_transactions')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->groupBy('status')
    ->get();

foreach ($statuses as $status) {
    echo "'{$status->status}': {$status->count} records\n";
}

// Test the actual query from UserFinancialReport
echo "\nTesting the query logic:\n";
$testQuery = UserTransaction::query()
    ->select(
        DB::raw("DATE_FORMAT(transaction_date, '%Y-%m-01') as month_date"),
        DB::raw("SUM(CASE WHEN transaction_type = '" . UserTransaction::TYPE_DEPOSIT . "' THEN amount ELSE 0 END) as deposits"),
        DB::raw("SUM(CASE WHEN transaction_type = '" . UserTransaction::TYPE_WITHDRAWAL . "' THEN amount ELSE 0 END) as withdrawals"),
    )
    ->where('status', UserTransaction::STATUS_DONE)
    ->groupBy('month_date')
    ->get();

echo "Query results:\n";
foreach ($testQuery as $result) {
    echo "Month: {$result->month_date}, Deposits: {$result->deposits}, Withdrawals: {$result->withdrawals}\n";
}

// Check sample records
echo "\nSample records:\n";
$samples = UserTransaction::take(5)->get(['transaction_type', 'amount', 'status', 'transaction_date']);
foreach ($samples as $sample) {
    echo "Type: '{$sample->transaction_type}', Amount: {$sample->amount}, Status: '{$sample->status}', Date: {$sample->transaction_date}\n";
}
