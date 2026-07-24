<?php

namespace App\Http\Requests;

class StoreClientNoteRequest extends \Illuminate\Foundation\Http\FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:5000'],
            'pinned' => ['sometimes', 'boolean'],
        ];
    }
}
