<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ProjectController extends BaseApiController
{
    protected string $model = Project::class;
    protected ?string $resource = ProjectResource::class;

    protected array $searchableFields = [
        'project_key',
        'title',
        'description',
        'unit',
        'compound'
    ];

    protected array $filterableFields = [
        'status',
        'stage',
        'type',
        'investment_type',
        'developer_id'
    ];

    protected array $sortableFields = [
        'id',
        'project_key',
        'title',
        'area',
        'total_contract_value',
        'reservation_date',
        'contract_date',
        'created_at',
        'updated_at'
    ];

    /**
     * Validate store request
     */
    protected function validateStoreRequest(Request $request): array
    {
        return $request->validate([
            'project_key' => 'required|string|max:50|unique:projects',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:100',
            'area' => 'nullable|numeric|min:0',
            'garden_area' => 'nullable|numeric|min:0',
            'compound' => 'nullable|string|max:255',
            'status' => 'required|string|max:50',
            'stage' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:50',
            'investment_type' => 'nullable|string|max:50',
            'reservation_date' => 'nullable|date',
            'contract_date' => 'nullable|date',
            'total_contract_value' => 'nullable|numeric|min:0',
            'years' => 'nullable|integer|min:0',
            'developer_id' => 'required|exists:developers,id',
            'notes' => 'nullable|string',
        ]);
    }

    /**
     * Validate update request
     */
    protected function validateUpdateRequest(Request $request, Model $resource): array
    {
        return $request->validate([
            'project_key' => 'sometimes|string|max:50|unique:projects,project_key,' . $resource->id,
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:100',
            'area' => 'nullable|numeric|min:0',
            'garden_area' => 'nullable|numeric|min:0',
            'compound' => 'nullable|string|max:255',
            'status' => 'sometimes|string|max:50',
            'stage' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:50',
            'investment_type' => 'nullable|string|max:50',
            'reservation_date' => 'nullable|date',
            'contract_date' => 'nullable|date',
            'total_contract_value' => 'nullable|numeric|min:0',
            'years' => 'nullable|integer|min:0',
            'developer_id' => 'sometimes|exists:developers,id',
            'notes' => 'nullable|string',
        ]);
    }
}
