<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoyaltyPointTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'points' => $this->points,
            'type' => $this->type,
            'description' => $this->description,
            'booking' => $this->whenLoaded('booking', fn () => $this->booking ? [
                'id' => $this->booking->id,
                'service_name' => $this->booking->service_name,
            ] : null),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
