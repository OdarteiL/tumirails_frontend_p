<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEstimationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled in the controller/service layer
        // to verify ownership and write permissions
        return true;
    }

    public function rules(): array
    {
        // Empty for now - future parameters may include:
        // - force_recalculate
        // - override_tariff_id
        // - custom_multipliers
        return [];
    }
}
