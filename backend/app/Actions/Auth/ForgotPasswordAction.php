<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordAction
{
    public function execute(string $email): ?array
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return null;
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->upsert(
            ['email' => $email, 'token' => Hash::make($token), 'created_at' => now()],
            ['email'],
            ['token', 'created_at'],
        );

        return ['user' => $user, 'token' => $token];
    }
}
