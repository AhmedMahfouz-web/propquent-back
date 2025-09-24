<?php

require __DIR__ . '/vendor/autoload.php';

try {
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "App created successfully\n";
    
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    echo "Kernel created successfully\n";
    
    $input = new Symfony\Component\Console\Input\ArgvInput();
    $output = new Symfony\Component\Console\Output\ConsoleOutput();
    
    echo "About to handle command\n";
    $status = $kernel->handle($input, $output);
    echo "Command handled with status: $status\n";
    
} catch (Exception $e) {
    echo "Exception caught: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Error caught: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
