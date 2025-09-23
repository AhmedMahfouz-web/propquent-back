<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTransactionResource extends JsonResource
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
            'financial_type' => $this->financial_type,
            'serving' => $this->serving,
            'amount' => $this->amount,
            'method' => $this->method,
            'reference_no' => $this->reference_no,
            'transaction_date' => $this->transaction_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'actual_date' => $this->actual_date?->toDateString(),
            'status' => $this->status,
            'note' => $this->note,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relationships (when loaded)
            'project' => new ProjectResource($this->whenLoaded('project')),
        ];
    }
}
