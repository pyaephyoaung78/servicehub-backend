<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'customer';
    }

    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:1',
            ],
            'payment_method' => [
                'required',
                'string',
                'max:100',
            ],
            'proof' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],
            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
