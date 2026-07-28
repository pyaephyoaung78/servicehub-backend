<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceProofResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'kind' => $this->kind, 'note' => $this->note, 'image' => ['name' => $this->image_original_name, 'mime_type' => $this->image_mime_type, 'size' => $this->image_size, 'url' => route('api.service-proofs.file', [$this->booking_id, $this->id])], 'captured_at' => $this->captured_at?->toISOString(), 'staff' => $this->whenLoaded('staffProfile', fn () => ['name' => $this->staffProfile->user?->name])];
    }
}
