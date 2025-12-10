<?php

namespace App\Actions\Auth;

use App\Models\ProviderDetail;
use App\Models\User;

class RegisterProviderAction
{
    public function execute(array $userData, array $providerData): array
    {
        $user = User::create([
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'other_names' => $userData['other_names'] ?? null,
            'email' => $userData['email'],
            'password' => $userData['password'],
            'phone' => $userData['phone'] ?? null,
            'address' => $userData['address'] ?? null,
            'role' => 'provider',
            'status' => 'active',
        ]);

        $provider = ProviderDetail::create([
            'user_id' => $user->id,
            'company_name' => $providerData['company_name'] ?? null,
            'business_registration' => $providerData['business_registration'],
            'service_areas' => $providerData['service_areas'],
            'certifications' => $providerData['certifications'] ?? null,
            'rating' => 0.00,
        ]);

        return [
            'user' => $user,
            'provider' => $provider,
        ];
    }
}
