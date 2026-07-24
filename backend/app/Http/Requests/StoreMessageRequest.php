<?php

namespace App\Http\Requests;

class StoreMessageRequest extends \Illuminate\Foundation\Http\FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('sendMessage', $this->route('conversation')) ?? false;
    }

    public function rules(): array
    {
        return ['body' => ['required', 'string', 'max:5000']];
    }
}
