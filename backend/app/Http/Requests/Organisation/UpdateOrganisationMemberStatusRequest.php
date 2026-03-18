<?php

namespace App\Http\Requests\Organisation;

use App\Rules\DifferentFromCurrentMemberStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganisationMemberStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $user = auth()->user();
        $targetMember = $this->route('member'); // assumes Route::patch('/{organisation}/members/{member}/status')

        $isSystemAdmin = $user->role === 'admin';
        $isOrgOwner = $targetMember->organisation->owner()?->id === $user->id;

        return $isSystemAdmin || $isOrgOwner;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $targetMember = $this->route('member');

        return [
            'status' => [
                'required',
                'in:active,suspended',
                new DifferentFromCurrentMemberStatus($targetMember),
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
