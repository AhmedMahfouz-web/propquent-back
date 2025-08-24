<?php

namespace App\Imports;

use App\Models\ProjectEvaluation;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Carbon\Carbon;

class ProjectEvaluationImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Find project by key or title
        $project = null;
        if (!empty($row['project_key'])) {
            $project = Project::where('key', $row['project_key'])->first();
        } elseif (!empty($row['project_title'])) {
            $project = Project::where('title', $row['project_title'])->first();
        }

        if (!$project) {
            throw new \Exception("Project not found for key: " . ($row['project_key'] ?? $row['project_title'] ?? 'N/A'));
        }

        return new ProjectEvaluation([
            'project_key' => $project->key,
            'evaluation_date' => !empty($row['evaluation_date']) ? Carbon::parse($row['evaluation_date']) : now(),
            'evaluation_amount' => !empty($row['evaluation_amount']) ? (float) $row['evaluation_amount'] : 0,
            'notes' => $row['notes'] ?? null,
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'project_key' => 'nullable|string|max:255',
            'project_title' => 'nullable|string|max:255',
            'evaluation_date' => 'required|date',
            'evaluation_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Custom validation to ensure either project_key or project_title is provided
     */
    public function prepareForValidation($data, $index)
    {
        if (empty($data['project_key']) && empty($data['project_title'])) {
            throw new \Exception("Either project_key or project_title must be provided at row " . ($index + 1));
        }

        return $data;
    }
}
