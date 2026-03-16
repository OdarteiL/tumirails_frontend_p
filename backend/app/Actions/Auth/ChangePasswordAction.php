<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ChangePasswordAction
{
    public function execute(User $user, string $currentPassword, string $newPassword): bool
    {
        if (! Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->update(['password' => Hash::make($newPassword)]);

        return true;
    }
}
