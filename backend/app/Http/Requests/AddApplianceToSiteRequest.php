<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddApplianceToSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appliance_id' => 'required|integer|exists:appliances,id',
            'quantity' => 'required|integer|min:1',
            'daily_usage_hours' => 'required|numeric|min:0|max:24',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'appliance_id.required' => 'Appliance ID is required',
            'appliance_id.exists' => 'Selected appliance does not exist',
            'quantity.min' => 'Quantity must be at least 1',
            'daily_usage_hours.min' => 'Daily usage hours cannot be negative',
            'daily_usage_hours.max' => 'Daily usage hours cannot exceed 24',
        ];
    }
}
