<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'invoice_no' => $this->invoice_no,

            'booking_id' => $this->booking_id,

            'customer' => $this->whenLoaded(
                'customer',
                fn () => [
                    'id' => $this->customer?->id,
                    'name' => $this->customer?->name,
                    'email' => $this->customer?->email,
                ]
            ),

            'issued_by' => $this->whenLoaded(
                'issuedBy',
                fn () => [
                    'id' => $this->issuedBy?->id,
                    'name' => $this->issuedBy?->name,
                    'email' => $this->issuedBy?->email,
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
                'paid_amount' => $this->paid_amount,
                'remaining_amount' => $this->remaining_amount,
            ],

            'payment_status' => $this->payment_status->value,

            'issued_at' => $this->issued_at?->toISOString(),

            'paid_at' => $this->paid_at?->toISOString(),

            'note' => $this->note,

            'booking' => new BookingResource(
                $this->whenLoaded('booking')
            ),

            'payments' => InvoicePaymentResource::collection(
                $this->whenLoaded('payments')
            ),

            'payment_proofs' => PaymentProofResource::collection(
                $this->whenLoaded('paymentProofs')
            ),
        ];
    }
}
