<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewPaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'review_note' => [
                Rule::requiredIf(
                    fn () => $this->routeIs('admin.payment-proofs.reject')
                ),
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
