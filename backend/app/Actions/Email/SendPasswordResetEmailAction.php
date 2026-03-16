<?php

namespace App\Actions\Email;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class SendPasswordResetEmailAction
{
    public function execute(User $user, string $token): void
    {
        $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:4200'), '/');
        $expiryMinutes = (int) config('auth.passwords.users.expire', 60);

        $resetUrl = $frontendUrl.'/reset-password?'.http_build_query([
            'token' => $token,
            'email' => $user->email,
        ]);

        Mail::to($user->email)->send(new PasswordResetMail($user, $resetUrl, $expiryMinutes));
    }
}
