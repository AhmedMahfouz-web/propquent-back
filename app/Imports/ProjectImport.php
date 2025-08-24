<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\Developer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProjectImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Find developer by name or create if not exists
        $developer = null;
        if (!empty($row['developer_name'])) {
            $developer = Developer::where('name', $row['developer_name'])->first();
            if (!$developer) {
                $developer = Developer::create([
                    'name' => $row['developer_name'],
                    'email' => $row['developer_email'] ?? null,
                    'phone' => !empty($row['developer_phone']) ? (string) $row['developer_phone'] : null,
                ]);
            }
        }

        return new Project([
            'project_key' => $row['project_key'] ?? null,
            'title' => $row['title'],
            'developer_id' => $developer?->id,
            'location' => $row['location'] ?? null,
            'type' => $row['type'] ?? null,
            'unit_no' => $row['unit_no'] ?? null,
            'project' => $row['project'] ?? null,
            'area' => !empty($row['area']) ? (float) $row['area'] : null,
            'garden_area' => !empty($row['garden_area']) ? (float) $row['garden_area'] : null,
            'bedrooms' => !empty($row['bedrooms']) ? (int) $row['bedrooms'] : null,
            'bathrooms' => !empty($row['bathrooms']) ? (int) $row['bathrooms'] : null,
            'floor' => $row['floor'] ?? null,
            'status' => $row['status'] ?? 'active',
            'stage' => $row['stage'] ?? null,
            'target_1' => $row['target_1'] ?? null,
            'target_2' => $row['target_2'] ?? null,
            'entry_date' => !empty($row['entry_date']) ? Carbon::parse($row['entry_date']) : null,
            'exit_date' => !empty($row['exit_date']) ? Carbon::parse($row['exit_date']) : null,
            'investment_type' => $row['investment_type'] ?? null,
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'project_key' => 'nullable|string|max:50|unique:projects,project_key',
            'developer_name' => 'nullable|string|max:255',
            'developer_email' => 'nullable|email|max:255',
            'developer_phone' => 'nullable|max:20',
            'location' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:100',
            'unit_no' => 'nullable|string|max:50',
            'project' => 'nullable|string|max:255',
            'area' => 'nullable|numeric|min:0',
            'garden_area' => 'nullable|numeric|min:0',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'floor' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'stage' => 'nullable|string|max:100',
            'target_1' => 'nullable|string|max:255',
            'target_2' => 'nullable|string|max:255',
            'entry_date' => 'nullable|date',
            'exit_date' => 'nullable|date',
            'investment_type' => 'nullable|string|max:100',
        ];
    }
}
