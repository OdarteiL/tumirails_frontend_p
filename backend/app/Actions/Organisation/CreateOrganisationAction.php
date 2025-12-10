<?php

namespace App\Actions\Organisation;

use App\Models\Organisation;
use App\Models\OrganisationInstallerDetail;
use App\Models\OrganisationMember;
use App\Models\OrganisationProviderDetail;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateOrganisationAction
{
    public function execute(array $data, User $user): Organisation
    {
        return DB::transaction(function () use ($data, $user) {
            // Create the organisation
            $organisation = Organisation::create([
                'name' => $data['name'],
                'type' => $data['type'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);

            // Create type-specific details
            if ($organisation->type === 'installer') {
                OrganisationInstallerDetail::create([
                    'organisation_id' => $organisation->id,
                    'license_number' => $data['license_number'],
                    'service_areas' => $data['service_areas'],
                    'certifications' => $data['certifications'] ?? null,
                    'years_experience' => $data['years_experience'],
                    'rating' => 0.00,
                ]);

                // Migrate user's installer details if they exist
                if ($user->installerDetail) {
                    $user->installerDetail->delete();
                }
            } elseif ($organisation->type === 'provider') {
                OrganisationProviderDetail::create([
                    'organisation_id' => $organisation->id,
                    'business_registration' => $data['business_registration'],
                    'service_areas' => $data['service_areas'],
                    'certifications' => $data['certifications'] ?? null,
                    'rating' => 0.00,
                ]);

                // Migrate user's provider details if they exist
                if ($user->providerDetail) {
                    $user->providerDetail->delete();
                }
            }

            // Add the creating user as the owner
            OrganisationMember::create([
                'organisation_id' => $organisation->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            // Transfer sites if requested
            if ($data['transfer_sites'] ?? false) {
                $user->sites()->update([
                    'owner_id' => $organisation->id,
                    'owner_type' => Organisation::class,
                ]);
            }

            return $organisation->load(['installerDetail', 'providerDetail', 'members.user']);
        });
    }
}
