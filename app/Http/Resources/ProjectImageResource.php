<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectImageResource extends JsonResource
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
            'image_path' => $this->image_path,
            'image_url' => $this->image_url,
            'alt_text' => $this->alt_text,
            'is_primary' => $this->is_primary,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relationships (when loaded)
            'project' => new ProjectResource($this->whenLoaded('project')),
        ];
    }
}
