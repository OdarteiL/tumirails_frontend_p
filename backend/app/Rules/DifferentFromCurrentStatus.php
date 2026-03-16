<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DifferentFromCurrentStatus implements ValidationRule
{
    public function __construct(protected User $user) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->user->status === $value) {
            $fail('The :attribute must be different from the current user status.');
        }
    }
}
