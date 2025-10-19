<?php

// Simple cache clearing script
// Run this by visiting: http://localhost/propquent-new/clear_caches.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "Clearing caches...\n";

try {
    // Clear view cache
    $kernel->call('view:clear');
    echo "✓ View cache cleared\n";
    
    // Clear config cache
    $kernel->call('config:clear');
    echo "✓ Config cache cleared\n";
    
    // Clear route cache
    $kernel->call('route:clear');
    echo "✓ Route cache cleared\n";
    
    // Clear application cache
    $kernel->call('cache:clear');
    echo "✓ Application cache cleared\n";
    
    echo "\nAll caches cleared successfully!\n";
    echo "You can now test the value correction modal.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Delete this file after use for security
unlink(__FILE__);
echo "Cache clearing script deleted.\n";
