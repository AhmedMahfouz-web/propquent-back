<?php

// Test the month order logic
$startMonth = '2024-01-01';
$endMonth = '2024-03-01';

echo "Testing month order logic:\n";
echo "Start: $startMonth, End: $endMonth\n\n";

// Simulate getMonthsInRange() logic
$start = new DateTime($startMonth);
$end = new DateTime($endMonth);
$interval = new DateInterval('P1M');
$period = new DatePeriod($start, $interval, $end->modify('+1 month'));
$months = [];
foreach ($period as $dt) {
    $months[] = $dt->format('Y-m-01');
}

echo "Original months array:\n";
print_r($months);

echo "\nAfter array_reverse (what getMonthsInRange returns):\n";
$allMonths = array_reverse($months);
print_r($allMonths);

echo "\nAfter another array_reverse (what monthsChronological becomes):\n";
$monthsChronological = array_reverse($allMonths);
print_r($monthsChronological);

echo "\nFirst month (end of chronological): " . end($monthsChronological) . "\n";
echo "This should be the OLDEST month for correct calculation\n";
