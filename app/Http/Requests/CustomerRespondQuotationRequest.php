<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRespondQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'customer';
    }

    public function rules(): array
    {
        return [
            'action' => [
                'required',
                Rule::in(['accept', 'reject']),
            ],

            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}