<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentProofResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'status' => $this->status->value,
            'note' => $this->note,
            'review_note' => $this->review_note,
            'proof' => [
                'file_name' => $this->proof_original_name,
                'mime_type' => $this->proof_mime_type,
                'size' => $this->proof_size,
            ],
            'submitted_at' => $this->created_at?->toISOString(),
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'invoice_payment_id' => $this->invoice_payment_id,
        ];
    }
}
