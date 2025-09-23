<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'unit' => $this->unit,
            'area' => $this->area,
            'garden_area' => $this->garden_area,
            'compound' => $this->compound,
            'status' => $this->status,
            'stage' => $this->stage,
            'type' => $this->type,
            'investment_type' => $this->investment_type,
            'reservation_date' => $this->reservation_date?->toDateString(),
            'contract_date' => $this->contract_date?->toDateString(),
            'total_contract_value' => $this->total_contract_value,
            'years' => $this->years,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relationships (when loaded)
            'developer' => new DeveloperResource($this->whenLoaded('developer')),
            'transactions_count' => $this->whenCounted('transactions'),
            'transactions' => ProjectTransactionResource::collection($this->whenLoaded('transactions')),
            'evaluations' => ProjectEvaluationResource::collection($this->whenLoaded('evaluations')),
            'images' => ProjectImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
