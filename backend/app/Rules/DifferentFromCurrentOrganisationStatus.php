<?php

namespace App\Rules;

use App\Models\Organisation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DifferentFromCurrentOrganisationStatus implements ValidationRule
{
    public function __construct(protected Organisation $organisation) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->organisation->status === $value) {
            $fail('The :attribute must be different from the current organisation status.');
        }
    }
}
