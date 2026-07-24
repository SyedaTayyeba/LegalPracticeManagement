<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'case_id' => ['required', 'string', Rule::exists('cases', 'uuid')->where('firm_id', $this->user()->firm_id)],
            'description' => ['required', 'string', 'max:500'],
            'minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'billable' => ['sometimes', 'boolean'],
            'entry_date' => ['required', 'date'],
        ];
    }
}
