<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Starting debug script...\n";

try {
    // Register the Composer autoloader
    echo "Including autoloader...\n";
    require __DIR__.'/vendor/autoload.php';
    echo "Autoloader included.\n";

    // Bootstrap Laravel application
    echo "Bootstrapping application...\n";
    $app = require_once __DIR__.'/bootstrap/app.php';
    echo "Application bootstrapped successfully.\n";

} catch (Throwable $e) {
    echo "An error occurred:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
