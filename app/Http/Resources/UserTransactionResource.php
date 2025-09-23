<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTransactionResource extends JsonResource
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
            'user_id' => $this->user_id,
            'transaction_type' => $this->transaction_type,
            'amount' => $this->amount,
            'method' => $this->method,
            'reference_no' => $this->reference_no,
            'transaction_date' => $this->transaction_date?->toDateString(),
            'actual_date' => $this->actual_date?->toDateString(),
            'status' => $this->status,
            'note' => $this->note,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relationships (when loaded)
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
