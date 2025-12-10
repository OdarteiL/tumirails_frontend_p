<?php

namespace App\Actions\Auth;

use App\Models\InstallerDetail;
use App\Models\User;

class RegisterInstallerAction
{
    public function execute(array $userData, array $installerData): array
    {
        $user = User::create([
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'other_names' => $userData['other_names'] ?? null,
            'email' => $userData['email'],
            'password' => $userData['password'],
            'phone' => $userData['phone'] ?? null,
            'address' => $userData['address'] ?? null,
            'role' => 'installer',
            'status' => 'active',
        ]);

        $installer = InstallerDetail::create([
            'user_id' => $user->id,
            'company_name' => $installerData['company_name'] ?? null,
            'license_number' => $installerData['license_number'],
            'service_areas' => $installerData['service_areas'],
            'certifications' => $installerData['certifications'] ?? null,
            'years_experience' => $installerData['years_experience'],
            'rating' => 0.00,
        ]);

        return [
            'user' => $user,
            'installer' => $installer,
        ];
    }
}
