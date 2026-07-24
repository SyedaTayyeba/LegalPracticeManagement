<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'case_type' => ['required', 'string', 'max:120'],
            'client_id' => [
                'required',
                'string',
                Rule::exists('clients', 'uuid')->where('firm_id', $this->user()->firm_id),
            ],
            'lead_lawyer_id' => [
                'nullable',
                'string',
                Rule::exists('users', 'uuid')->where('firm_id', $this->user()->firm_id),
            ],
            'opposing_party' => ['nullable', 'string', 'max:255'],
            'opposing_counsel' => ['nullable', 'string', 'max:255'],
            'court_name' => ['nullable', 'string', 'max:255'],
            'court_case_number' => ['nullable', 'string', 'max:120'],
            'court_jurisdiction' => ['nullable', 'string', 'max:120'],
            'opened_on' => ['nullable', 'date'],
            'filed_on' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:10000'],
            'team_user_ids' => ['sometimes', 'array'],
            'team_user_ids.*' => [
                'string',
                Rule::exists('users', 'uuid')->where('firm_id', $this->user()->firm_id),
            ],
        ];
    }
}
