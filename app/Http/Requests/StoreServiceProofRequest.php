<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceProofRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'staff'; }

    public function rules(): array
    {
        return [
            'kind' => ['required', Rule::in(['before', 'after'])],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
