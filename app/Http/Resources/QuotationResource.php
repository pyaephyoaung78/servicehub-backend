<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'quotation_no' => $this->quotation_no,

            'booking_id' => $this->booking_id,

            'customer' => $this->whenLoaded(
                'customer',
                fn () => [
                    'id' => $this->customer?->id,
                    'name' => $this->customer?->name,
                    'email' => $this->customer?->email,
                ]
            ),

            'created_by' => $this->whenLoaded(
                'createdBy',
                fn () => [
                    'id' => $this->createdBy?->id,
                    'name' => $this->createdBy?->name,
                    'email' => $this->createdBy?->email,
                ]
            ),

            'service' => [
                'name' => $this->service_name,
                'price' => $this->service_price,
            ],

            'amounts' => [
                'service_price' => $this->service_price,
                'extra_fee' => $this->extra_fee,
                'discount_amount' => $this->discount_amount,
                'total_amount' => $this->total_amount,
            ],

            'status' => $this->status->value,

            'admin_note' => $this->admin_note,

            'customer_response_note' =>
                $this->customer_response_note,

            'valid_until' =>
                $this->valid_until?->toISOString(),

            'sent_at' =>
                $this->sent_at?->toISOString(),

            'accepted_at' =>
                $this->accepted_at?->toISOString(),

            'rejected_at' =>
                $this->rejected_at?->toISOString(),

            'expired_at' =>
                $this->expired_at?->toISOString(),

            'booking' => new BookingResource(
                $this->whenLoaded('booking')
            ),
        ];
    }
}