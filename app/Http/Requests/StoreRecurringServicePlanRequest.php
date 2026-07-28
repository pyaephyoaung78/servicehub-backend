<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecurringServicePlanRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'customer'; }

    public function rules(): array
    {
        return ['interval_days' => ['required', 'integer', 'in:30,60,90,180,365'], 'reminder_days_before' => ['required', 'integer', 'min:1', 'max:30']];
    }
}
