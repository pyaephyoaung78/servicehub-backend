<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'staff';
    }

    public function rules(): array
    {
        return [
            'is_available' => [
                'required',
                'boolean',
            ],
        ];
    }
}