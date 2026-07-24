<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Any firm staff member (owner/lawyer/paralegal) may create clients; portal
        // clients cannot create other client records.
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['individual', 'organization'])],
            'first_name' => ['required_if:type,individual', 'nullable', 'string', 'max:120'],
            'last_name' => ['required_if:type,individual', 'nullable', 'string', 'max:120'],
            'organization_name' => ['required_if:type,organization', 'nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'secondary_phone' => ['nullable', 'string', 'max:30'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:120'],
            'intake_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
