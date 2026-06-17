<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_category_id' => [
                'required',
                'integer',
                'exists:service_categories,id',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:services,name',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'base_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'estimated_duration_minutes' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}