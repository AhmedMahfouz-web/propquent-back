<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Memory Debug Script ===\n";
echo "Initial memory: " . memory_get_usage(true) / 1024 / 1024 . " MB\n";
echo "Memory limit: " . ini_get('memory_limit') . "\n\n";

try {
    echo "Testing Project model creation...\n";

    // Test creating a simple project
    $project = new App\Models\Project();
    $project->key = 'test-' . time();
    $project->title = 'Test Project';
    $project->developer_id = 1; // Assuming developer exists
    $project->location = 'Test Location';
    $project->type = 'Test Type';
    $project->unit_no = '001';
    $project->project = 'Test Project Name';
    $project->area = 100;
    $project->bedrooms = 2;
    $project->bathrooms = 1;
    $project->floor = '1';
    $project->status = 'On-going';
    $project->stage = 'Buying';
    $project->investment_type = 'Test';

    echo "Memory before save: " . memory_get_usage(true) / 1024 / 1024 . " MB\n";

    // This is where the infinite loop might occur
    $project->save();

    echo "Memory after save: " . memory_get_usage(true) / 1024 / 1024 . " MB\n";
    echo "Project created successfully with ID: " . $project->id . "\n";

    // Clean up
    $project->delete();
    echo "Test completed successfully!\n";
} catch (Exception $e) {
    echo "Error occurred: " . $e->getMessage() . "\n";
    echo "Memory at error: " . memory_get_usage(true) / 1024 / 1024 . " MB\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nPeak memory usage: " . memory_get_peak_usage(true) / 1024 / 1024 . " MB\n";
