<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingWorkStatusRequest extends FormRequest
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
                    'mark_on_the_way',
                    'start',
                    'complete',
                ]),
            ],
        ];
    }
}