<?php

namespace App\Actions\Organisation;

use App\Models\OrganisationMember;

class RemoveOrganisationMemberAction
{
    public function execute(OrganisationMember $member): void
    {
        // Prevent removing the owner
        if ($member->role === 'owner') {
            throw new \Exception('Cannot remove the organisation owner.');
        }

        $member->delete();
    }
}
