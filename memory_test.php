<?php

// Simple memory test script
echo "Current memory limit: " . ini_get('memory_limit') . "\n";
echo "Current memory usage: " . memory_get_usage(true) / 1024 / 1024 . " MB\n";
echo "Peak memory usage: " . memory_get_peak_usage(true) / 1024 / 1024 . " MB\n";

// Test if we can increase memory limit
ini_set('memory_limit', '512M');
echo "New memory limit: " . ini_get('memory_limit') . "\n";
