<?php

namespace App\Imports;

use App\Models\ProjectTransaction;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\FromSheet;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ProjectTransactionImport implements ToModel, WithHeadingRow, FromSheet
{
    private $currentRow = 1; // Track current row number
    private $processedRows = 0; // Track how many rows we actually process
    
    /**
     * Specify which sheet to import from
     * This will look for sheets named "project transactions" (case insensitive)
     */
    public function sheet(): string
    {
        return 'project transactions';
    }
    
    /**
     * Clean header names by removing descriptive text in parentheses
     */
    private function cleanHeaderName($header): string
    {
        return trim(explode('(', $header)[0]);
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
        
        // Log that we're processing from the correct sheet
        if ($this->currentRow === 2) { // First data row (after headers)
            Log::info("Starting import from 'project transactions' sheet");
        }
        
        // Skip empty rows
        if (empty(array_filter($row))) {
            Log::info("Row {$this->currentRow}: Skipping empty row");
            return null;
        }

        $projectKey = $this->getRowValue($row, 'project_key');
        $financialType = $this->getRowValue($row, 'financial_type');
        $rawAmount = $this->getRowValue($row, 'amount');
        $amount = $this->parseAmount($rawAmount);
        
        // Debug: Log what we're processing
        Log::info("Row {$this->currentRow}: Processing Excel row", [
            'row_number' => $this->currentRow,
            'project_key' => $projectKey,
            'financial_type' => $financialType,
            'amount' => $amount,
            'raw_amount' => $rawAmount,
            'all_row_data' => $row
        ]);
        
        // Skip if no project key found
        if (empty($projectKey)) {
            Log::warning("Row {$this->currentRow}: Skipping row - no project key found", ['row' => $row]);
            return null;
        }

        // Skip if no amount was provided in Excel (empty cell)
        if (empty($rawAmount) || $rawAmount === null || $rawAmount === '') {
            Log::warning("Row {$this->currentRow}: Skipping row - no amount provided in Excel", ['project_key' => $projectKey, 'raw_amount' => $rawAmount]);
            return null;
        }

        // Skip if amount is still invalid after parsing
        if ($amount <= 0) {
            Log::warning("Row {$this->currentRow}: Skipping row - invalid amount after parsing", ['amount' => $amount, 'project_key' => $projectKey, 'raw_amount' => $rawAmount]);
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
        
        Log::info("Row {$this->currentRow}: Creating transaction", [
            'project_key' => $projectKey,
            'financial_type' => $financialType ?? 'expense',
            'amount' => $amount,
            'total_processed' => $this->processedRows
        ]);

        // Log summary every 5 transactions or if it's the first one
        if ($this->processedRows === 1 || $this->processedRows % 5 === 0) {
            Log::info("Import progress: {$this->processedRows} transactions processed so far");
        }

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
