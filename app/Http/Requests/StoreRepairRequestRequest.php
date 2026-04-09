<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRepairRequestRequest extends FormRequest
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
            'asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            'problem_type' => ['required', 'string', 'max:100'],
            'problem_summary' => ['required', 'string', 'max:255'],
            'troubleshooting_note' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,normal,high,critical'],
        ];
    }
}
