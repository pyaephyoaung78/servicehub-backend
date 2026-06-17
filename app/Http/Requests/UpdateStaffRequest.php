<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $staffProfile = $this->route('staffProfile');
        $userId = $staffProfile?->user_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
                    ->ignore($userId),
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'bio' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'is_available' => [
                'required',
                'boolean',
            ],

            'service_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'service_ids.*' => [
                'integer',
                'distinct',
                'exists:services,id',
            ],
        ];
    }
}