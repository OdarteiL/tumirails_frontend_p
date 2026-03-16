<?php

namespace App\Rules;

use App\Models\OrganisationMember;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DifferentFromCurrentMemberStatus implements ValidationRule
{
    public function __construct(protected OrganisationMember $member) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->member->status === $value) {
            $fail('The :attribute must be different from the current member status.');
        }
    }
}
