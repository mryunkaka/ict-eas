<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmailRequestRequest extends FormRequest
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
            'employee_name' => ['required', 'string', 'max:255'],
            'department_name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'justification' => ['required', 'string'],
            'diketahui_dept_head_name' => ['nullable', 'string', 'max:255'],
            'diketahui_dept_head_title' => ['nullable', 'string', 'max:255'],
            'diketahui_div_head_name' => ['nullable', 'string', 'max:255'],
            'diketahui_div_head_title' => ['nullable', 'string', 'max:255'],
            'disetujui_hrga_head_name' => ['nullable', 'string', 'max:255'],
            'disetujui_hrga_head_title' => ['nullable', 'string', 'max:255'],
            'pelaksana_ict_head_name' => ['nullable', 'string', 'max:255'],
            'pelaksana_ict_head_title' => ['nullable', 'string', 'max:255'],
        ];
    }
}
