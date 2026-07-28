<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecurringServicePlanRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'customer'; }

    public function rules(): array
    {
        return ['is_active' => ['required', 'boolean']];
    }
}
