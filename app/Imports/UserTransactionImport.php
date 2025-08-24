<?php

namespace App\Imports;

use App\Models\UserTransaction;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class UserTransactionImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new UserTransaction([
            'user_id' => User::where('custom_id', $row['user_id'])->first()->id,
            'transaction_type' => $row['type'],
            'amount' => !empty($row['amount']) ? (float) $row['amount'] : 0,
            'transaction_date' => $this->parseDate($row['transaction_date']),
            'description' => $row['description'] ?? null,
            'status' => $row['status'] ?? UserTransaction::STATUS_DONE,
        ]);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', Rule::exists('users', 'custom_id')],
            'type' => ['required', 'string', Rule::in(UserTransaction::getAvailableTransactionTypes())],
            'amount' => ['required', 'numeric', 'min:0'],
            'transaction_date' => ['required'],
            'description' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(UserTransaction::getAvailableStatuses())],
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
