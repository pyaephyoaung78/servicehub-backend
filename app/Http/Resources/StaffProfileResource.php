<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'user' => $this->whenLoaded(
                'user',
                fn () => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'role' => $this->user->role,
                ]
            ),

            'phone' => $this->phone,
            'bio' => $this->bio,
            'is_active' => $this->is_active,
            'is_available' => $this->is_available,

            'services' => ServiceResource::collection(
                $this->whenLoaded('services')
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}