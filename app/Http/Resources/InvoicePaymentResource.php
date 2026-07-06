<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoicePaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'amount' => $this->amount,

            'payment_method' => $this->payment_method,

            'note' => $this->note,

            'paid_at' => $this->paid_at?->toISOString(),

            'received_by' => $this->whenLoaded(
                'receivedBy',
                fn () => [
                    'id' => $this->receivedBy?->id,
                    'name' => $this->receivedBy?->name,
                    'email' => $this->receivedBy?->email,
                ]
            ),
        ];
    }
}