<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecommendationBundleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'total_cost' => ['required', 'numeric'],
            'currency' => ['nullable', 'string', 'max:3'],
            'metadata' => ['nullable', 'array'],
            'components' => ['required', 'array', 'min:1'],
            'components.*.hardware_id' => ['required', 'integer', 'exists:hardware,id'],
            'components.*.quantity' => ['nullable', 'integer', 'min:1'],
            'components.*.total_cost' => ['nullable', 'numeric'],
            'components.*.role' => ['nullable', 'string'],
            'components.*.rationale' => ['nullable', 'string'],
        ];
    }
}
