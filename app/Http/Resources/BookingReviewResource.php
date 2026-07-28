<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'booking_id' => $this->booking_id, 'rating' => $this->rating, 'comment' => $this->comment, 'status' => $this->status, 'created_at' => $this->created_at?->toISOString(), 'service' => $this->whenLoaded('service', fn () => ['id' => $this->service->id, 'name' => $this->service->name]), 'staff' => $this->whenLoaded('staffProfile', fn () => ['name' => $this->staffProfile?->user?->name])];
    }
}
