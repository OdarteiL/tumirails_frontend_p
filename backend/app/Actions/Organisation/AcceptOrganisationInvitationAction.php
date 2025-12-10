<?php

namespace App\Actions\Organisation;

use App\Models\OrganisationInvitation;
use App\Models\OrganisationMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AcceptOrganisationInvitationAction
{
    public function execute(string $token, User $user): OrganisationMember
    {
        return DB::transaction(function () use ($token, $user) {
            $invitation = OrganisationInvitation::where('token', $token)
                ->where('email', $user->email)
                ->firstOrFail();

            if (! $invitation->isValid()) {
                throw new \Exception('This invitation is no longer valid.');
            }

            // Check if user is already a member
            $existingMember = OrganisationMember::where('organisation_id', $invitation->organisation_id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingMember) {
                throw new \Exception('You are already a member of this organisation.');
            }

            // Create the membership
            $member = OrganisationMember::create([
                'organisation_id' => $invitation->organisation_id,
                'user_id' => $user->id,
                'role' => $invitation->role,
                'joined_at' => now(),
            ]);

            // Mark invitation as accepted
            $invitation->update(['accepted_at' => now()]);

            return $member->load(['organisation', 'user']);
        });
    }
}
