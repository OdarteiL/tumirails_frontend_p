<?php

namespace App\Actions\Auth;

use App\Models\User;

class GenerateAuthTokenAction
{
    public function execute(User $user, string $tokenName = 'auth_token'): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }
}
