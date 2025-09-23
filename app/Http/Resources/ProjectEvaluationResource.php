<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectEvaluationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_key' => $this->project_key,
            'evaluation_date' => $this->evaluation_date?->toDateString(),
            'market_value' => $this->market_value,
            'evaluator_name' => $this->evaluator_name,
            'evaluation_method' => $this->evaluation_method,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relationships (when loaded)
            'project' => new ProjectResource($this->whenLoaded('project')),
        ];
    }
}
