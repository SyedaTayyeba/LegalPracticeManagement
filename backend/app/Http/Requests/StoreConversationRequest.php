<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? $this->user()?->isClient() ?? false;
    }

    public function rules(): array
    {
        $firmId = $this->user()->firm_id;

        return [
            'subject' => ['nullable', 'string', 'max:255'],
            'case_id' => ['nullable', 'string', Rule::exists('cases', 'uuid')->where('firm_id', $firmId)],
            'client_id' => ['nullable', 'string', Rule::exists('clients', 'uuid')->where('firm_id', $firmId)],
            'participant_ids' => ['sometimes', 'array'],
            'participant_ids.*' => ['string', Rule::exists('users', 'uuid')->where('firm_id', $firmId)],
            'body' => ['required', 'string', 'max:5000'],
        ];
    }
}
