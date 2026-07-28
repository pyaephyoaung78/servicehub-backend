<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RebookBookingRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'customer'; }
    public function rules(): array { return ['scheduled_at' => ['required', 'date', 'after:now']]; }
}
