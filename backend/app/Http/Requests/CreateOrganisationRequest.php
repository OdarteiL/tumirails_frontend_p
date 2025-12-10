<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrganisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['installer', 'provider', 'customer'])],
            'email' => ['required', 'email', 'max:255', 'unique:organisations,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],

            // Installer-specific fields
            'license_number' => ['required_if:type,installer', 'string', 'max:100', 'unique:organisation_installer_details,license_number'],
            'years_experience' => ['required_if:type,installer', 'integer', 'min:0', 'max:100'],

            // Provider-specific fields
            'business_registration' => ['required_if:type,provider', 'string', 'max:100', 'unique:organisation_provider_details,business_registration'],

            // Common optional fields for installer and provider
            'service_areas' => ['required_if:type,installer', 'required_if:type,provider', 'array', 'min:1'],
            'service_areas.*' => ['string', 'max:100'],
            'certifications' => ['nullable', 'array'],
            'certifications.*' => ['string', 'max:255'],
            
            // Site transfer option
            'transfer_sites' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'license_number.required_if' => 'License number is required for installer organisations.',
            'years_experience.required_if' => 'Years of experience is required for installer organisations.',
            'business_registration.required_if' => 'Business registration is required for provider organisations.',
            'service_areas.required_if' => 'Service areas are required for installer and provider organisations.',
        ];
    }
}
