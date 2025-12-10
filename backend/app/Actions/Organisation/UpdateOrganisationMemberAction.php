<?php

namespace App\Actions\Organisation;

use App\Models\OrganisationMember;

class UpdateOrganisationMemberAction
{
    public function execute(OrganisationMember $member, array $data): OrganisationMember
    {
        // Prevent changing owner role
        if ($member->role === 'owner') {
            throw new \Exception('Cannot change the role of the organisation owner.');
        }

        $member->update([
            'role' => $data['role'],
        ]);

        return $member->fresh(['user', 'organisation']);
    }
}
