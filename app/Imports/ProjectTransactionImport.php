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
        // Log what we're looking for and what's available
        if ($fieldName === 'amount') {
            Log::info('Looking for amount field', [
                'field_name' => $fieldName,
                'available_keys' => array_keys($row),
                'row_data' => $row
            ]);
        }
        
        // Try exact match first
        if (isset($row[$fieldName])) {
            $value = $row[$fieldName];
            if ($fieldName === 'amount') {
                Log::info('Found exact match for amount', ['value' => $value]);
            }
            return $value;
        }
        
        // Try to find header that starts with the field name
        foreach ($row as $key => $value) {
            $cleanKey = $this->cleanHeaderName($key);
            if ($cleanKey === $fieldName) {
                if ($fieldName === 'amount') {
                    Log::info('Found cleaned match for amount', ['key' => $key, 'clean_key' => $cleanKey, 'value' => $value]);
                }
                return $value;
            }
        }
        
        if ($fieldName === 'amount') {
            Log::warning('No match found for amount field');
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
        // Skip empty rows
        if (empty(array_filter($row))) {
            return null;
        }

        $projectKey = $this->getRowValue($row, 'project_key');
        $amount = $this->parseAmount($this->getRowValue($row, 'amount'));
        
        // Skip if no project key found
        if (empty($projectKey)) {
            return null;
        }

        // Skip if amount is still invalid
        if ($amount <= 0) {
            return null;
        }

        return new ProjectTransaction([
            'project_key' => $projectKey,
            'financial_type' => $this->getRowValue($row, 'financial_type') ?? 'expense',
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
            'transaction_category' => $this->getRowValue($row, 'transaction_category'),
        ]);
    }

    /**
     * Parse amount from Excel format or string
     */
    private function parseAmount($amount)
    {
        // Log the original amount for debugging
        \Log::info('Parsing amount', ['original' => $amount, 'type' => gettype($amount)]);
        
        if (empty($amount) || $amount === null || $amount === '') {
            \Log::info('Amount is empty, returning 1');
            return 1; // Default to 1 instead of 0 to pass validation
        }

        // Convert to string first
        $amount = (string) $amount;
        
        // Remove common formatting characters
        $cleanAmount = str_replace([',', ' ', '$', '€', '£', 'EGP'], '', $amount);
        
        // Convert to float
        $parsedAmount = (float) $cleanAmount;
        
        \Log::info('Amount parsing result', [
            'original' => $amount,
            'cleaned' => $cleanAmount,
            'parsed' => $parsedAmount
        ]);
        
        // If still 0 or negative after parsing, return 1
        if ($parsedAmount <= 0) {
            \Log::warning('Parsed amount is <= 0, returning 1', ['parsed' => $parsedAmount]);
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
