<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingMessageRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()?->role, ['customer', 'staff'], true); }

    public function rules(): array
    {
        return [
            'body' => ['nullable', 'string', 'max:2000', 'required_without:attachment'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240', 'required_without:body'],
        ];
    }
}
