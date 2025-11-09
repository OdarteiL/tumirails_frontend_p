<?php

namespace App\Services;

use App\Actions\Auth\RegisterUserAction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function __construct(
        private RegisterUserAction $registerUserAction
    ) {}

    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = $this->registerUserAction->execute($data);
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user,
                'access_token' => $token,
            ];
        });
    }

    public function login(array $credentials): ?array
    {
        if (!Auth::attempt($credentials)) {
            return null;
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'access_token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}