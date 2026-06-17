<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RespondToAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'staff';
    }

    public function rules(): array
    {
        return [
            'action' => [
                'required',
                Rule::in([
                    'accept',
                    'reject',
                ]),
            ],

            'response_note' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}