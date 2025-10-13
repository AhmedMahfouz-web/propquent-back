<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Illuminate\Support\Facades\Log;

class ProjectTransactionSheetImport implements WithMultipleSheets, SkipsUnknownSheets
{
    public $imports = [];
    public $debugInfo = [];

    public function sheets(): array
    {
        $this->debugInfo[] = "Looking for sheets: 'project transactions', 'Project Transactions', 'PROJECT TRANSACTIONS'";

        $this->imports['project transactions'] = new ProjectTransactionImport();
        $this->imports['Project Transactions'] = new ProjectTransactionImport();
        $this->imports['PROJECT TRANSACTIONS'] = new ProjectTransactionImport();

        return $this->imports;
    }

    public function onUnknownSheet($sheetName)
    {
        // Skip unknown sheets and log them
        $this->debugInfo[] = "Skipping unknown sheet: '{$sheetName}' (not a project transactions sheet)";
    }
}
