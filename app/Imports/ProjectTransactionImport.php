<?php

namespace App\Imports;

use App\Models\ProjectTransaction;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Validation\Rule;

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
        // Skip empty rows
        if (empty(array_filter($row))) {
            return null;
        }

        $projectKey = $this->getRowValue($row, 'project_key');
        
        // Skip if no project key found
        if (empty($projectKey)) {
            return null;
        }

        return new ProjectTransaction([
            'project_key' => $projectKey,
            'financial_type' => $this->getRowValue($row, 'financial_type') ?? 'expense',
            'serving' => $this->getRowValue($row, 'serving'),
            'what' => $this->getRowValue($row, 'what'),
            'amount' => !empty($this->getRowValue($row, 'amount')) ? (float) $this->getRowValue($row, 'amount') : 0,
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
