<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('case')) ?? false;
    }

    public function rules(): array
    {
        $firmId = $this->user()->firm_id;

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'case_type' => ['sometimes', 'string', 'max:120'],
            'lead_lawyer_id' => ['sometimes', 'nullable', 'string', Rule::exists('users', 'uuid')->where('firm_id', $firmId)],
            'opposing_party' => ['sometimes', 'nullable', 'string', 'max:255'],
            'opposing_counsel' => ['sometimes', 'nullable', 'string', 'max:255'],
            'court_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'court_case_number' => ['sometimes', 'nullable', 'string', 'max:120'],
            'court_jurisdiction' => ['sometimes', 'nullable', 'string', 'max:120'],
            'opened_on' => ['sometimes', 'nullable', 'date'],
            'filed_on' => ['sometimes', 'nullable', 'date'],
            'description' => ['sometimes', 'nullable', 'string', 'max:10000'],
        ];
    }
}
