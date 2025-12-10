<?php

namespace App\Services;

use App\Actions\Auth\GenerateAuthTokenAction;
use App\Actions\Auth\GetAuthenticatedUserAction;
use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterInstallerAction;
use App\Actions\Auth\RegisterProviderAction;
use App\Actions\Auth\RegisterUserAction;
use App\Actions\Auth\RevokeAuthTokenAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function __construct(
        private RegisterUserAction $registerUserAction,
        private RegisterInstallerAction $registerInstallerAction,
        private RegisterProviderAction $registerProviderAction,
        private LoginUserAction $loginUserAction,
        private GenerateAuthTokenAction $generateAuthTokenAction,
        private RevokeAuthTokenAction $revokeAuthTokenAction,
        private GetAuthenticatedUserAction $getAuthenticatedUserAction
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

        if (! $user) {
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

    public function getAuthenticatedUser(User $user): User
    {
        return $this->getAuthenticatedUserAction->execute($user);
    }

    public function registerInstaller(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $userData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'other_names' => $data['other_names'] ?? null,
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ];

            $installerData = [
                'company_name' => $data['company_name'],
                'license_number' => $data['license_number'],
                'service_areas' => $data['service_areas'],
                'certifications' => $data['certifications'] ?? null,
                'years_experience' => $data['years_experience'],
            ];

            $result = $this->registerInstallerAction->execute($userData, $installerData);
            $token = $this->generateAuthTokenAction->execute($result['user']);

            return [
                'user' => $result['user'],
                'installer' => $result['installer'],
                'access_token' => $token,
            ];
        });
    }

    public function registerProvider(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $userData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'other_names' => $data['other_names'] ?? null,
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ];

            $providerData = [
                'company_name' => $data['company_name'],
                'business_registration' => $data['business_registration'],
                'service_areas' => $data['service_areas'],
                'certifications' => $data['certifications'] ?? null,
            ];

            $result = $this->registerProviderAction->execute($userData, $providerData);
            $token = $this->generateAuthTokenAction->execute($result['user']);

            return [
                'user' => $result['user'],
                'provider' => $result['provider'],
                'access_token' => $token,
            ];
        });
    }
}
