<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'staff_profile_id' => [
                'required',
                'integer',
                'exists:staff_profiles,id',
            ],

            'admin_note' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}