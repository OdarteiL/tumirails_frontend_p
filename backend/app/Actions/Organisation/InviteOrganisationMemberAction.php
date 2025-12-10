<?php

namespace App\Actions\Organisation;

use App\Models\Organisation;
use App\Models\OrganisationInvitation;
use App\Models\User;
use Carbon\Carbon;

class InviteOrganisationMemberAction
{
    public function execute(Organisation $organisation, array $data, User $inviter): OrganisationInvitation
    {
        // Check if user is already a member
        $existingMember = $organisation->members()->where('user_id', function ($query) use ($data) {
            $query->select('id')
                ->from('users')
                ->where('email', $data['email'])
                ->limit(1);
        })->exists();

        if ($existingMember) {
            throw new \Exception('User is already a member of this organisation.');
        }

        // Check if there's already a pending invitation
        $existingInvitation = OrganisationInvitation::where('organisation_id', $organisation->id)
            ->where('email', $data['email'])
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvitation) {
            throw new \Exception('There is already a pending invitation for this email.');
        }

        // Create the invitation
        $invitation = OrganisationInvitation::create([
            'organisation_id' => $organisation->id,
            'email' => $data['email'],
            'role' => $data['role'],
            'token' => OrganisationInvitation::generateToken(),
            'invited_by' => $inviter->id,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        return $invitation->load(['organisation', 'inviter']);
    }
}
