<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Illuminate\Support\Facades\Log;

class ProjectTransactionSheetImport implements WithMultipleSheets, SkipsUnknownSheets
{
    public function sheets(): array
    {
        return [
            'project transactions' => new ProjectTransactionImport(),
            'Project Transactions' => new ProjectTransactionImport(), // Case variation
            'PROJECT TRANSACTIONS' => new ProjectTransactionImport(), // Case variation
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        // Skip unknown sheets silently
        Log::info("Skipping unknown sheet: {$sheetName}");
    }
}
