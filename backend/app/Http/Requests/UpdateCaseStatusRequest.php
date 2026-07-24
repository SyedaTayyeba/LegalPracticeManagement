<?php

namespace App\Http\Requests;

use App\Models\CaseFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCaseStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('case')) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(CaseFile::STATUSES)],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
