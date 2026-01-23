<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReverseEstimationRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', Rule::in(['prepaid', 'postpaid'])],
            'month' => ['required_if:type,postpaid', 'date_format:Y-m'],
            'start_date' => ['required_if:type,prepaid', 'date'],
            'end_date' => ['required_if:type,prepaid', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'month.required_if' => 'A month (YYYY-MM) is required for post-paid estimations.',
            'start_date.required_if' => 'A start date is required for pre-paid estimations.',
            'end_date.required_if' => 'An end date is required for pre-paid estimations.',
        ];
    }
}
