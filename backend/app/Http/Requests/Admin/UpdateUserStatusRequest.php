<?php

namespace App\Http\Requests\Admin;

use App\Rules\DifferentFromCurrentStatus;
use App\Rules\NotSelfSuspension;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $targetUser = $this->route('user');

        return [
            'status' => [
                'required',
                'in:active,suspended',
                new DifferentFromCurrentStatus($targetUser),
                new NotSelfSuspension($targetUser, auth()->user()),
            ],
            'reason' => 'nullable|string|max:500',
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
            'status.required' => 'Status is required',
            'status.in' => 'Status must be either active or suspended',
            'reason.max' => 'Reason cannot exceed 500 characters',
        ];
    }
}
