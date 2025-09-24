<?php

// Script to find and fix all Filament resources with old route() syntax

$resourcesDir = __DIR__ . '/app/Filament/Resources';
$resources = glob($resourcesDir . '/*Resource.php');

echo "Checking " . count($resources) . " resources...\n";

foreach ($resources as $resourceFile) {
    $content = file_get_contents($resourceFile);
    $resourceName = basename($resourceFile, '.php');
    
    echo "Checking $resourceName...\n";
    
    // Check if it has route() calls in getPages method
    if (preg_match('/getPages.*?return\s*\[(.*?)\]/s', $content, $matches)) {
        $pagesArray = $matches[1];
        
        if (strpos($pagesArray, '::route(') !== false) {
            echo "  ❌ Found route() syntax in $resourceName\n";
            
            // Show the problematic lines
            $lines = explode("\n", $content);
            $inGetPages = false;
            $braceCount = 0;
            
            foreach ($lines as $lineNum => $line) {
                if (strpos($line, 'getPages') !== false) {
                    $inGetPages = true;
                }
                
                if ($inGetPages) {
                    if (strpos($line, '::route(') !== false) {
                        echo "    Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
                    }
                    
                    $braceCount += substr_count($line, '{') - substr_count($line, '}');
                    if ($braceCount <= 0 && strpos($line, '}') !== false) {
                        $inGetPages = false;
                    }
                }
            }
        } else {
            echo "  ✅ $resourceName looks good\n";
        }
    }
}

echo "\nDone checking resources.\n";
