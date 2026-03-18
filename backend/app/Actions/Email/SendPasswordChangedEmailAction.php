<?php

namespace App\Actions\Email;

use App\Mail\PasswordChangedMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class SendPasswordChangedEmailAction
{
    public function execute(User $user): void
    {
        Mail::to($user->email)->send(new PasswordChangedMail($user));
    }
}
