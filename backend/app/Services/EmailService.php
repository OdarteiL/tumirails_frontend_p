<?php

namespace App\Services;

use App\Actions\Email\SendPasswordChangedEmailAction;
use App\Actions\Email\SendPasswordResetEmailAction;
use App\Models\User;

class EmailService
{
    public function __construct(
        private SendPasswordResetEmailAction $sendPasswordResetEmailAction,
        private SendPasswordChangedEmailAction $sendPasswordChangedEmailAction,
    ) {}

    public function sendPasswordReset(User $user, string $token): void
    {
        $this->sendPasswordResetEmailAction->execute($user, $token);
    }

    public function sendPasswordChanged(User $user): void
    {
        $this->sendPasswordChangedEmailAction->execute($user);
    }
}
