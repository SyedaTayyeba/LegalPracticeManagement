<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        $firmId = $this->user()->firm_id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'case_id' => ['nullable', 'string', Rule::exists('cases', 'uuid')->where('firm_id', $firmId)],
            'assigned_to' => ['nullable', 'string', Rule::exists('users', 'uuid')->where('firm_id', $firmId)],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
