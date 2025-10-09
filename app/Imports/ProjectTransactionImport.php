<?php

namespace App\Imports;

use App\Models\ProjectTransaction;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ProjectTransactionImport implements ToModel, WithHeadingRow
{
    private $currentRow = 1; // Track current row number
    private $processedRows = 0; // Track how many rows we actually process
    public $debugInfo = []; // Store debug information to show on website
    
    /**
     * Clean header names by removing descriptive text in parentheses and underscores
     */
    private function cleanHeaderName($header): string
    {
        // First remove text in parentheses
        $cleaned = trim(explode('(', $header)[0]);
        
        // Then extract the base field name from descriptive headers
        // e.g., "project_key_required_select_from_dropdown" -> "project_key"
        $fieldMappings = [
            'project_key_required_select_from_dropdown' => 'project_key',
            'project_name_optional_select_from_dropdown' => 'project_name',
            'developer_name_optional_see_projects_reference' => 'developer_name',
            'financial_type_required' => 'financial_type',
            'serving_optional' => 'serving',
            'what_optional' => 'what',
            'amount_required' => 'amount',
            'method_optional' => 'method',
            'reference_no_optional' => 'reference_no',
            'status_required' => 'status',
            'transaction_date_required' => 'transaction_date',
            'due_date_optional' => 'due_date',
            'actual_date_optional' => 'actual_date',
            'note_optional' => 'note',
            'transaction_category_optional' => 'transaction_category',
        ];
        
        return $fieldMappings[$cleaned] ?? $cleaned;
    }

    /**
     * Get value from row using flexible header matching
     */
    private function getRowValue(array $row, string $fieldName)
    {
        // Try exact match first
        if (isset($row[$fieldName])) {
            return $row[$fieldName];
        }
        
        // Try to find header that starts with the field name
        foreach ($row as $key => $value) {
            $cleanKey = $this->cleanHeaderName($key);
            if ($cleanKey === $fieldName) {
                return $value;
            }
        }
        
        return null;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $this->currentRow++; // Increment row counter
        
        // Collect debug info for website display
        if ($this->currentRow === 2) { // First data row (after headers)
            $this->debugInfo[] = "Starting project transaction import processing";
        }
        
        // Skip empty rows
        if (empty(array_filter($row))) {
            $this->debugInfo[] = "Row {$this->currentRow}: Skipping empty row";
            return null;
        }

        $projectKey = $this->getRowValue($row, 'project_key');
        $financialType = $this->getRowValue($row, 'financial_type');
        $rawAmount = $this->getRowValue($row, 'amount');
        $amount = $this->parseAmount($rawAmount);
        
        // Show column mapping for debugging
        if ($this->currentRow === 2) {
            $mappedColumns = [];
            foreach (array_keys($row) as $originalColumn) {
                $mappedColumn = $this->cleanHeaderName($originalColumn);
                if ($originalColumn !== $mappedColumn) {
                    $mappedColumns[] = "'{$originalColumn}' -> '{$mappedColumn}'";
                }
            }
            if (!empty($mappedColumns)) {
                $this->debugInfo[] = "Column mappings: " . implode(', ', $mappedColumns);
            }
        }
        
        // Collect debug info for website display
        $this->debugInfo[] = "Row {$this->currentRow}: Found data - Project: '{$projectKey}', Type: '{$financialType}', Amount: '{$rawAmount}' -> {$amount}";
        
        // Show field extraction details
        $this->debugInfo[] = "Row {$this->currentRow}: Field extraction - project_key: '{$this->getRowValue($row, 'project_key')}', financial_type: '{$this->getRowValue($row, 'financial_type')}', amount: '{$this->getRowValue($row, 'amount')}'";
        
        $this->debugInfo[] = "Row {$this->currentRow}: Available columns: " . implode(', ', array_keys($row));
        
        // Skip if no project key found
        if (empty($projectKey)) {
            $this->debugInfo[] = "Row {$this->currentRow}: ❌ SKIPPED - No project key found";
            return null;
        }

        // Skip if no amount was provided in Excel (empty cell)
        if (empty($rawAmount) || $rawAmount === null || $rawAmount === '') {
            $this->debugInfo[] = "Row {$this->currentRow}: ❌ SKIPPED - No amount provided (project: {$projectKey})";
            return null;
        }

        // Skip if amount is still invalid after parsing
        if ($amount <= 0) {
            $this->debugInfo[] = "Row {$this->currentRow}: ❌ SKIPPED - Invalid amount after parsing (project: {$projectKey}, raw: {$rawAmount}, parsed: {$amount})";
            return null;
        }

        $transaction = new ProjectTransaction([
            'project_key' => $projectKey,
            'financial_type' => $financialType ?? 'expense',
            'serving' => $this->getRowValue($row, 'serving'),
            'what' => $this->getRowValue($row, 'what'),
            'amount' => $amount,
            'due_date' => !empty($this->getRowValue($row, 'due_date')) ? $this->parseDate($this->getRowValue($row, 'due_date')) : null,
            'actual_date' => !empty($this->getRowValue($row, 'actual_date')) ? $this->parseDate($this->getRowValue($row, 'actual_date')) : null,
            'transaction_date' => $this->parseDate($this->getRowValue($row, 'transaction_date') ?? now()),
            'method' => $this->getRowValue($row, 'method'),
            'reference_no' => $this->getRowValue($row, 'reference_no'),
            'status' => $this->getRowValue($row, 'status') ?? 'pending',
            'note' => $this->getRowValue($row, 'note'),
        ]);

        $this->processedRows++;
        
        $this->debugInfo[] = "Row {$this->currentRow}: ✅ SUCCESS - Creating transaction (project: {$projectKey}, type: " . ($financialType ?? 'expense') . ", amount: {$amount})";
        $this->debugInfo[] = "Total transactions processed so far: {$this->processedRows}";

        return $transaction;
    }

    /**
     * Parse amount from Excel format or string
     */
    private function parseAmount($amount)
    {
        if (empty($amount) || $amount === null || $amount === '') {
            return 0; // Return 0 for empty amounts so we can catch them
        }

        // Convert to string first
        $amount = (string) $amount;
        
        // Remove common formatting characters
        $cleanAmount = str_replace([',', ' ', '$', '€', '£', 'EGP'], '', $amount);
        
        // Convert to float
        $parsedAmount = (float) $cleanAmount;
        
        // If still 0 or negative after parsing, return 1
        if ($parsedAmount <= 0) {
            return 1;
        }
        
        return $parsedAmount;
    }

    /**
     * Parse date from Excel format or string
     */
    private function parseDate($date)
    {
        if (empty($date)) {
            return now();
        }

        // If it's already a valid date string, parse it with Carbon
        if (is_string($date)) {
            try {
                return \Carbon\Carbon::parse($date);
            } catch (\Exception $e) {
                return now();
            }
        }

        // If it's a numeric Excel timestamp, convert it
        if (is_numeric($date)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);
            } catch (\Exception $e) {
                return now();
            }
        }

        return now();
    }
}
