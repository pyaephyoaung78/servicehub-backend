<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
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
                'nullable',
                'boolean',
            ],

            'is_available' => [
                'nullable',
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