<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequestRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'background' => ['required', 'string'],
            'scope' => ['required', 'string'],
            'expected_outcome' => ['required', 'string'],
            'priority' => ['required', 'in:low,normal,high'],
            'target_date' => ['nullable', 'date'],
        ];
    }
}
