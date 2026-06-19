<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'customer' => $this->whenLoaded(
                'customer',
                fn() => [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                    'email' => $this->customer->email,
                ]
            ),

            'service' => [
                'id' => $this->service_id,

                'current_service' => new ServiceResource(
                    $this->whenLoaded('service')
                ),

                // Historical values captured during booking.
                'booked_name' => $this->service_name,
                'booked_price' => $this->service_price,
            ],

            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'phone' => $this->phone,
            'address' => $this->address,
            'customer_note' => $this->customer_note,
            'status' => $this->status->value,

            'workflow' => [
                'on_the_way_at' =>
                $this->on_the_way_at?->toISOString(),

                'started_at' =>
                $this->started_at?->toISOString(),

                'completed_at' =>
                $this->completed_at?->toISOString(),
            ],

            'closure' => [
                'cancellation_reason' =>
                $this->cancellation_reason,

                'cancelled_at' =>
                $this->cancelled_at?->toISOString(),

                'cancelled_by' => $this->whenLoaded(
                    'cancelledBy',
                    fn() => [
                        'id' => $this->cancelledBy?->id,
                        'name' => $this->cancelledBy?->name,
                        'role' => $this->cancelledBy?->role,
                    ]
                ),

                'rejection_reason' =>
                $this->rejection_reason,

                'rejected_at' =>
                $this->rejected_at?->toISOString(),

                'rejected_by' => $this->whenLoaded(
                    'rejectedBy',
                    fn() => [
                        'id' => $this->rejectedBy?->id,
                        'name' => $this->rejectedBy?->name,
                        'role' => $this->rejectedBy?->role,
                    ]
                ),
            ],

            'latest_assignment' => new BookingAssignmentResource(
                $this->whenLoaded('latestAssignment')
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
