<?php

namespace App\Services;

use App\Actions\Auth\GenerateAuthTokenAction;
use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Actions\Auth\RevokeAuthTokenAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function __construct(
        private RegisterUserAction $registerUserAction,
        private LoginUserAction $loginUserAction,
        private GenerateAuthTokenAction $generateAuthTokenAction,
        private RevokeAuthTokenAction $revokeAuthTokenAction
    ) {}

    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = $this->registerUserAction->execute($data);
            $token = $this->generateAuthTokenAction->execute($user);

            return [
                'user' => $user,
                'access_token' => $token,
            ];
        });
    }

    public function login(array $credentials): ?array
    {
        $user = $this->loginUserAction->execute($credentials);

        if (!$user) {
            return null;
        }

        $token = $this->generateAuthTokenAction->execute($user);

        return [
            'user' => $user,
            'access_token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $this->revokeAuthTokenAction->execute($user);
    }
}