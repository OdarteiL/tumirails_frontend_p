<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotSelfSuspension implements ValidationRule
{
    public function __construct(protected User $targetUser, protected User $adminUser) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->targetUser->id === $this->adminUser->id && $value === 'suspended') {
            $fail('Admins cannot suspend themselves.');
        }
    }
}
