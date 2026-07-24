<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isFirmOwner() ?? false;
    }

    public function rules(): array
    {
        $firmId = $this->user()->firm_id;

        return [
            'client_id' => ['required', 'string', Rule::exists('clients', 'uuid')->where('firm_id', $firmId)],
            'case_id' => ['nullable', 'string', Rule::exists('cases', 'uuid')->where('firm_id', $firmId)],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'tax_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            // Unbilled time entries and expenses to pull onto this invoice.
            'time_entry_ids' => ['sometimes', 'array'],
            'time_entry_ids.*' => ['string'],
            'expense_ids' => ['sometimes', 'array'],
            'expense_ids.*' => ['string'],
            // Free-form manual line items (e.g. flat fees).
            'manual_line_items' => ['sometimes', 'array'],
            'manual_line_items.*.description' => ['required_with:manual_line_items', 'string', 'max:255'],
            'manual_line_items.*.quantity' => ['required_with:manual_line_items', 'numeric', 'min:0.01'],
            'manual_line_items.*.unit_price' => ['required_with:manual_line_items', 'numeric', 'min:0'],
        ];
    }
}
