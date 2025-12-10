<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterInstallerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'other_names' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'license_number' => ['required', 'string', 'max:255', 'unique:installer_details'],
            'service_areas' => ['required', 'array', 'min:1'],
            'service_areas.*' => ['required', 'string', 'max:255'],
            'certifications' => ['nullable', 'array'],
            'certifications.*' => ['string', 'max:255'],
            'years_experience' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }
}
