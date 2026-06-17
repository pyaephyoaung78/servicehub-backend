<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'customer';
    }

    public function rules(): array
    {
        return [
            'service_id' => [
                'required',
                'integer',
                Rule::exists('services', 'id')
                    ->where(fn ($query) => $query->where('is_active', true)),
            ],

            'scheduled_at' => [
                'required',
                'date',
                'after:now',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'address' => [
                'required',
                'string',
                'max:2000',
            ],

            'customer_note' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.exists' =>
                'The selected service does not exist or is not currently available.',

            'scheduled_at.after' =>
                'The booking schedule must be in the future.',
        ];
    }
}