<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'custom_id' => $this->custom_id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'country' => $this->country,
            'profile_picture_url' => $this->profile_picture_url,
            'is_active' => $this->is_active,
            'email_verified' => $this->email_verified,
            'theme_color' => $this->theme_color,
            'custom_theme_color' => $this->custom_theme_color,
            'last_login_at' => $this->last_login_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relationships (when loaded)
            'transactions_count' => $this->whenCounted('transactions'),
            'transactions' => UserTransactionResource::collection($this->whenLoaded('transactions')),
        ];
    }
}
