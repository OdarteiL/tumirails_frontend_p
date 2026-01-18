<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuestEstimationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow guest users
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'appliances' => ['required', 'array'],
            'appliances.*.id' => ['nullable', 'integer', 'exists:appliances,id'],
            'appliances.*.name' => ['nullable', 'string', 'max:255'],
            'appliances.*.wattage' => ['required_without:appliances.*.id', 'numeric', 'min:0'],
            'appliances.*.quantity' => ['required', 'integer', 'min:1'],
            'appliances.*.daily_usage_hours' => ['required', 'numeric', 'min:0', 'max:24'],
        ];
    }
}
