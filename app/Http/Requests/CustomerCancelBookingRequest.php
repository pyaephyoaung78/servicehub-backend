<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerCancelBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'customer';
    }

    public function rules(): array
    {
        return [
            'reason' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}