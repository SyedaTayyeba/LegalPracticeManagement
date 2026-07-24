<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        $firmId = $this->user()->firm_id;

        return [
            'file' => ['required', 'file', 'max:51200'], // 50 MB ceiling per upload
            'name' => ['nullable', 'string', 'max:255'],
            'category' => ['required', Rule::in(Document::CATEGORIES)],
            'case_id' => ['nullable', 'string', Rule::exists('cases', 'uuid')->where('firm_id', $firmId)],
            'client_id' => ['nullable', 'string', Rule::exists('clients', 'uuid')->where('firm_id', $firmId)],
            'folder_id' => ['nullable', 'string', Rule::exists('document_folders', 'uuid')->where('firm_id', $firmId)],
            'client_visible' => ['sometimes', 'boolean'],
        ];
    }
}
