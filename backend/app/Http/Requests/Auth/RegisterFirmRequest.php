<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterFirmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public registration endpoint
    }

    public function rules(): array
    {
        return [
            // Firm
            'firm_name' => ['required', 'string', 'max:255'],
            'plan' => ['required', 'in:solo,professional,enterprise'],

            // Owner
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()->symbols()],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
