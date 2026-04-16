<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreIctRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->canCreateIctRequest();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'requester_name' => ['required', 'string', 'max:255'],
            'department_name' => ['required', 'string', 'max:255'],
            'needed_at' => ['required', 'date'],
            'request_category' => ['required', 'in:hardware,software,accessories'],
            'priority' => ['required', 'in:urgent,normal'],
            'quotation_mode' => ['required', 'in:global,per_item'],
            'is_pta_request' => ['nullable', 'boolean'],
            'justification' => ['required', 'string'],
            'additional_budget_reason' => ['nullable', 'string'],
            'pta_budget_not_listed_reason' => ['nullable', 'required_if:is_pta_request,1', 'string'],
            'pta_additional_budget_reason' => ['nullable', 'required_if:is_pta_request,1', 'string'],
            'drafted_by_name' => ['nullable', 'required_if:is_pta_request,1', 'string', 'max:255'],
            'drafted_by_title' => ['nullable', 'required_if:is_pta_request,1', 'string', 'max:255'],
            'acknowledged_by_name' => ['nullable', 'required_if:is_pta_request,1', 'string', 'max:255'],
            'acknowledged_by_title' => ['nullable', 'required_if:is_pta_request,1', 'string', 'max:255'],
            'approved_1_name' => ['nullable', 'required_if:is_pta_request,1', 'string', 'max:255'],
            'approved_1_title' => ['nullable', 'required_if:is_pta_request,1', 'string', 'max:255'],
            'approved_2_name' => ['nullable', 'required_if:is_pta_request,1', 'string', 'max:255'],
            'approved_2_title' => ['nullable', 'required_if:is_pta_request,1', 'string', 'max:255'],
            'approved_3_name' => ['nullable', 'required_if:is_pta_request,1', 'string', 'max:255'],
            'approved_3_title' => ['nullable', 'required_if:is_pta_request,1', 'string', 'max:255'],
            'approved_4_name' => ['nullable', 'required_if:is_pta_request,1', 'string', 'max:255'],
            'approved_4_title' => ['nullable', 'required_if:is_pta_request,1', 'string', 'max:255'],
            'global_quotations' => ['nullable', 'array', 'size:3'],
            'global_quotations.*.vendor_name' => ['nullable', 'string', 'max:255'],
            'global_quotations.*.attachment' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'global_quotations.*.current_attachment_name' => ['nullable', 'string', 'max:255'],
            'global_quotations.*.current_attachment_path' => ['nullable', 'string', 'max:255'],
            'global_quotations.*.current_attachment_mime' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.item_category' => ['nullable', 'string', 'max:100'],
            'items.*.brand_type' => ['nullable', 'string', 'max:255'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.estimated_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.item_notes' => ['nullable', 'string'],
            'items.*.photo_name' => ['nullable', 'string', 'max:15'],
            'items.*.photo' => ['nullable', 'image', 'max:512'],
            'items.*.current_photo_name' => ['nullable', 'string', 'max:255'],
            'items.*.current_photo_path' => ['nullable', 'string', 'max:255'],
            'items.*.quotations' => ['nullable', 'array', 'size:3'],
            'items.*.quotations.*.vendor_name' => ['nullable', 'string', 'max:255'],
            'items.*.quotations.*.attachment' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'items.*.quotations.*.current_attachment_name' => ['nullable', 'string', 'max:255'],
            'items.*.quotations.*.current_attachment_path' => ['nullable', 'string', 'max:255'],
            'items.*.quotations.*.current_attachment_mime' => ['nullable', 'string', 'max:255'],
        ];
    }
}
