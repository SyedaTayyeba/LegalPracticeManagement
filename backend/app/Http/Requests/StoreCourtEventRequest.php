<?php

namespace App\Http\Requests;

use App\Models\CourtEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourtEventRequest extends FormRequest
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
            'event_type' => ['required', Rule::in(CourtEvent::EVENT_TYPES)],
            'case_id' => ['nullable', 'string', Rule::exists('cases', 'uuid')->where('firm_id', $firmId)],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'lead_lawyer_id' => ['nullable', 'string', Rule::exists('users', 'uuid')->where('firm_id', $firmId)],
            'attendee_ids' => ['sometimes', 'array'],
            'attendee_ids.*' => ['string', Rule::exists('users', 'uuid')->where('firm_id', $firmId)],
            'force' => ['sometimes', 'boolean'], // bypass conflict warning if the user confirms anyway
        ];
    }
}
