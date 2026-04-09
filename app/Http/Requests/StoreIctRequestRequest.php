<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreIctRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'request_category' => ['required', 'string', 'max:100'],
            'priority' => ['required', 'in:urgent,normal'],
            'needed_at' => ['nullable', 'date'],
            'justification' => ['required', 'string'],
            'additional_budget_reason' => ['nullable', 'string'],
            'item_name' => ['required', 'string', 'max:255'],
            'brand_type' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'estimated_price' => ['nullable', 'numeric', 'min:0'],
            'item_notes' => ['nullable', 'string'],
        ];
    }
}
