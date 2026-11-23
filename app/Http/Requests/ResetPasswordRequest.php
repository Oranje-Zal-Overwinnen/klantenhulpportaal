<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class ResetPasswordRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'token.required' => 'De resetlink is ongeldig of verlopen.',
            'email.required' => 'Vul je e-mailadres in.',
            'email.email' => 'Vul een geldig e-mailadres in.',
            'password.required' => 'Vul een nieuw wachtwoord in.',
            'password.min' => 'Het wachtwoord moet minimaal 8 tekens bevatten.',
            'password.confirmed' => 'De wachtwoorden komen niet overeen.',
        ];
    }
}
