<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApplianceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Middleware handles admin check, admins can modify any appliance
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'default_wattage' => ['sometimes', 'numeric', 'min:0'],
            'default_usage_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'metadata' => ['nullable', 'array'],
            'metadata.efficiency_rating' => ['nullable', 'string', 'max:10'],
            'metadata.notes' => ['nullable', 'string', 'max:500'],
            'is_public' => ['nullable', 'boolean'], // Admins can change visibility
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
            'name.max' => 'Appliance name must not exceed 255 characters',
            'category_id.exists' => 'Selected category does not exist',
            'default_wattage.min' => 'Default wattage must be at least 0',
            'default_usage_hours.min' => 'Default usage hours must be at least 0',
            'default_usage_hours.max' => 'Default usage hours cannot exceed 24',
            'metadata.efficiency_rating.max' => 'Efficiency rating must not exceed 10 characters',
            'metadata.notes.max' => 'Notes must not exceed 500 characters',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure metadata is an array if provided
        if ($this->has('metadata') && ! is_array($this->metadata)) {
            $this->merge([
                'metadata' => json_decode($this->metadata, true) ?? [],
            ]);
        }
    }
}
