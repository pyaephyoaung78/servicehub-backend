<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminStoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'booking_id' => [
                'required',
                'integer',
                'exists:bookings,id',
            ],
            'extra_fee' => [
                'nullable',
                'numeric',
                'decimal:0,2',
                'min:0',
            ],
            'discount_amount' => [
                'nullable',
                'numeric',
                'decimal:0,2',
                'min:0',
            ],
            'admin_note' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'valid_until' => [
                'nullable',
                'date',
                'after:now',
            ],
        ];
    }
}
