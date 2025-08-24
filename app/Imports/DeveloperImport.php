<?php

namespace App\Imports;

use App\Models\Developer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;

class DeveloperImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Developer([
            'name' => $row['name'],
            'email' => $row['email'] ?? null,
            'phone' => !empty($row['phone']) ? (string) $row['phone'] : null,
            'address' => $row['address'] ?? null,
            'website' => $row['website'] ?? null,
            'description' => $row['description'] ?? null,
            'status' => $row['status'] ?? 'active',
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:developers,name',
            'email' => 'nullable|email|max:255|unique:developers,email',
            'phone' => 'nullable|max:20',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:active,inactive',
        ];
    }
}
