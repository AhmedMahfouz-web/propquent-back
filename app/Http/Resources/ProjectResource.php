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
            // Primary identifiers
            'id' => $this->id,
            'project_key' => $this->project_key,
            'key' => $this->key,
            'title' => $this->title,
            
            // Location information
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'map_location' => $this->map_location,
            
            // Property details
            'type' => $this->type,
            'unit_no' => $this->unit_no,
            'project' => $this->project,
            'area' => $this->area,
            'garden_area' => $this->garden_area,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'floor' => $this->floor,
            
            // Status and stage
            'status' => $this->status,
            'stage' => $this->stage,
            'target_1' => $this->target_1,
            'target_2' => $this->target_2,
            
            // Dates
            'entry_date' => $this->entry_date?->toDateString(),
            'exit_date' => $this->exit_date?->toDateString(),
            'reservation_date' => $this->reservation_date?->toDateString(),
            'contract_date' => $this->contract_date?->toDateString(),
            
            // Financial information
            'investment_type' => $this->investment_type,
            'years_of_installment' => $this->years_of_installment,
            'total_contract_value' => $this->total_contract_value,
            
            // Additional information
            'document' => $this->document,
            'notes' => $this->notes,
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relationships (when loaded)
            'developer' => new DeveloperResource($this->whenLoaded('developer')),
            'compound' => $this->whenLoaded('compound', function () {
                return [
                    'id' => $this->compound?->id,
                    'name' => $this->compound?->name,
                    'location' => $this->compound?->location,
                ];
            }),
            'transactions_count' => $this->whenCounted('transactions'),
            'transactions' => ProjectTransactionResource::collection($this->whenLoaded('transactions')),
            'evaluations' => ProjectEvaluationResource::collection($this->whenLoaded('evaluations')),
            
            // Media/Images (Spatie Media Library)
            'images' => $this->when($this->relationLoaded('media'), function () {
                return $this->getMedia('images')->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'name' => $media->name,
                        'file_name' => $media->file_name,
                        'mime_type' => $media->mime_type,
                        'size' => $media->size,
                        'url' => $media->getUrl(),
                        'thumbnail_url' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
                        'preview_url' => $media->hasGeneratedConversion('preview') ? $media->getUrl('preview') : $media->getUrl(),
                        'created_at' => $media->created_at?->toISOString(),
                    ];
                });
            }),
        ];
    }
}
