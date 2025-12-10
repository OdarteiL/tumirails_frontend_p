<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganisationMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in(['admin', 'installer', 'provider', 'customer'])],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Role must be one of: admin, installer, provider, customer.',
        ];
    }
}
