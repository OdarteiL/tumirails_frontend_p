<?php

namespace Database\Seeders;

use App\Models\Organisation;
use App\Models\OrganisationMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrganisationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a demo installer organisation user
        $installerOwner = User::updateOrCreate(
            ['email' => env('INSTALLER_OWNER_EMAIL', 'installer@tumi.com')],
            [
                'first_name' => 'Installer',
                'last_name' => 'Owner',
                'role' => 'installer',
                'status' => 'active',
                'password' => Hash::make(env('INSTALLER_OWNER_PASSWORD', 'installer123')),
            ]
        );

        // Create installer organisation
        $installerOrg = Organisation::updateOrCreate(
            ['email' => env('INSTALLER_ORG_EMAIL', 'installer-org@tumi.com')],
            [
                'name' => env('INSTALLER_ORG_NAME', 'Demo Installer Co.'),
                'type' => 'installer',
                'phone' => null,
                'address' => null,
                'status' => 'active',
            ]
        );

        // Add owner membership
        OrganisationMember::updateOrCreate(
            [
                'organisation_id' => $installerOrg->id,
                'user_id' => $installerOwner->id,
            ],
            [
                'role' => 'owner',
                'status' => 'active',
                'joined_at' => now(),
            ]
        );

        // Create a demo provider organisation user
        $providerOwner = User::updateOrCreate(
            ['email' => env('PROVIDER_OWNER_EMAIL', 'provider@tumi.com')],
            [
                'first_name' => 'Provider',
                'last_name' => 'Owner',
                'role' => 'provider',
                'status' => 'active',
                'password' => Hash::make(env('PROVIDER_OWNER_PASSWORD', 'provider123')),
            ]
        );

        // Create provider organisation
        $providerOrg = Organisation::updateOrCreate(
            ['email' => env('PROVIDER_ORG_EMAIL', 'provider-org@tumi.com')],
            [
                'name' => env('PROVIDER_ORG_NAME', 'Demo Provider Ltd.'),
                'type' => 'provider',
                'phone' => null,
                'address' => null,
                'status' => 'active',
            ]
        );

        // Add owner membership
        OrganisationMember::updateOrCreate(
            [
                'organisation_id' => $providerOrg->id,
                'user_id' => $providerOwner->id,
            ],
            [
                'role' => 'owner',
                'status' => 'active',
                'joined_at' => now(),
            ]
        );

        $this->command->info("Installer organisation created: {$installerOrg->name} (owner: {$installerOwner->email})");
        $this->command->info("Provider organisation created: {$providerOrg->name} (owner: {$providerOwner->email})");
        $this->command->warn('Remember to change the default passwords in production!');
    }
}
