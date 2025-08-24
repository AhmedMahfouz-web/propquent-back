<?php

namespace App\Imports;

use App\Models\ProjectTransaction;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class ProjectTransactionImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new ProjectTransaction([
            'project_key' => $row['project_key'],
            'financial_type' => $row['financial_type'],
            'serving' => $row['serving'],
            'amount' => !empty($row['amount']) ? (float) $row['amount'] : 0,
            'due_date' => !empty($row['due_date']) ? $this->parseDate($row['due_date']) : null,
            'actual_date' => !empty($row['actual_date']) ? $this->parseDate($row['actual_date']) : null,
            'transaction_date' => $this->parseDate($row['transaction_date']),
            'method' => $row['method'] ?? null,
            'reference_no' => $row['reference_no'] ?? null,
            'status' => $row['status'] ?? 'pending',
            'note' => $row['note'] ?? null,
            'transaction_category' => $row['transaction_category'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'project_key' => ['required', 'string', Rule::exists('projects', 'key')],
            'financial_type' => ['required', 'string', Rule::in(ProjectTransaction::getAvailableFinancialTypes())],
            'serving' => ['required', 'string', Rule::in(ProjectTransaction::getAvailableServingTypes())],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'actual_date' => ['nullable', 'date'],
            'transaction_date' => ['required'],
            'method' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(ProjectTransaction::getAvailableStatuses())],
            'note' => ['nullable', 'string'],
            'transaction_category' => ['nullable', 'string', 'max:255'],
        ];
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
