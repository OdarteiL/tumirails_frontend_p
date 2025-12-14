<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEstimationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled in the controller/service layer
        // to verify site ownership and permissions
        return true;
    }

    public function rules(): array
    {
        return [
            'site_id' => ['required', 'integer', 'exists:sites,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'site_id.required' => 'Site ID is required',
            'site_id.exists' => 'The selected site does not exist',
        ];
    }
}
