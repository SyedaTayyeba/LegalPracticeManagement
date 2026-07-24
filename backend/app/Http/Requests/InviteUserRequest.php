<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only Firm Owners may invite. Lawyers/paralegals cannot add staff.
        return $this->user()?->isFirmOwner() ?? false;
    }

    public function rules(): array
    {
        $invitableRoles = array_map(fn (UserRole $r) => $r->value, UserRole::invitableRoles());

        return [
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->where('firm_id', $this->user()->firm_id),
            ],
            'role' => ['required', Rule::in($invitableRoles)],
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
