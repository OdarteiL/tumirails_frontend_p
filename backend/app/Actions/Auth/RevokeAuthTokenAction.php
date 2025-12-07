<?php

namespace App\Actions\Auth;

use App\Models\User;

class RevokeAuthTokenAction
{
    public function execute(User $user): bool
    {
        $token = $user->currentAccessToken();
        
        if ($token) {
            return (bool) $token->delete();
        }
        
        return false;
    }
}
