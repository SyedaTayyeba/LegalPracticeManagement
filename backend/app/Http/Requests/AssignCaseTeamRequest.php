<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignCaseTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('case')) ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'string',
                Rule::exists('users', 'uuid')->where('firm_id', $this->user()->firm_id),
            ],
            'role_on_case' => ['required', Rule::in(['lead', 'support'])],
        ];
    }
}
