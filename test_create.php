<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Model Creation ===\n";
echo "Memory limit: " . ini_get('memory_limit') . "\n";
echo "Initial memory: " . memory_get_usage(true) / 1024 / 1024 . " MB\n\n";

try {
    // Test User creation
    echo "Creating User...\n";
    $user = new App\Models\User();
    $user->full_name = 'Test User ' . time();
    $user->email = 'test' . time() . '@example.com';
    $user->password_hash = bcrypt('password');
    $user->save();
    echo "User created successfully with ID: " . $user->id . "\n";
    echo "Memory after User creation: " . memory_get_usage(true) / 1024 / 1024 . " MB\n\n";

    // Test Developer creation
    echo "Creating Developer...\n";
    $developer = new App\Models\Developer();
    $developer->name = 'Test Developer ' . time();
    $developer->save();
    echo "Developer created successfully with ID: " . $developer->id . "\n";
    echo "Memory after Developer creation: " . memory_get_usage(true) / 1024 / 1024 . " MB\n\n";

    // Clean up
    $user->delete();
    $developer->delete();
    echo "Cleanup completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Memory at error: " . memory_get_usage(true) / 1024 / 1024 . " MB\n";
}

echo "\nPeak memory: " . memory_get_peak_usage(true) / 1024 / 1024 . " MB\n";
