<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        $firmId = $this->user()->firm_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'case_id' => ['nullable', 'string', Rule::exists('cases', 'uuid')->where('firm_id', $firmId)],
            'parent_folder_id' => ['nullable', 'string', Rule::exists('document_folders', 'uuid')->where('firm_id', $firmId)],
        ];
    }
}
