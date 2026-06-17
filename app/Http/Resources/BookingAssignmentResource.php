<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,

            'booking' => new BookingResource(
                $this->whenLoaded('booking')
            ),

            'staff' => $this->whenLoaded(
                'staffProfile',
                fn() => [
                    'id' => $this->staffProfile->id,
                    'name' =>
                    $this->staffProfile->user?->name,
                    'email' =>
                    $this->staffProfile->user?->email,
                    'phone' =>
                    $this->staffProfile->phone,
                    'is_active' =>
                    $this->staffProfile->is_active,
                    'is_available' =>
                    $this->staffProfile->is_available,
                ]
            ),

            'assigned_by' => $this->whenLoaded(
                'assignedBy',
                fn() => [
                    'id' => $this->assignedBy->id,
                    'name' => $this->assignedBy->name,
                ]
            ),

            'admin_note' => $this->admin_note,
            'staff_response_note' =>
            $this->staff_response_note,

            'assigned_at' =>
            $this->assigned_at?->toISOString(),

            'responded_at' =>
            $this->responded_at?->toISOString(),

            'cancelled_at' =>
            $this->cancelled_at?->toISOString(),

            'created_at' =>
            $this->created_at?->toISOString(),
        ];
    }
}
