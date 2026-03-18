<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordAction
{
    public function execute(string $email, string $token, string $newPassword): bool
    {
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $record) {
            return false;
        }

        $expiryMinutes = (int) config('auth.passwords.users.expire', 60);

        if (now()->subMinutes($expiryMinutes)->greaterThan($record->created_at)) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return false;
        }

        if (! Hash::check($token, $record->token)) {
            return false;
        }

        User::where('email', $email)->update(['password' => Hash::make($newPassword)]);

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return true;
    }
}
