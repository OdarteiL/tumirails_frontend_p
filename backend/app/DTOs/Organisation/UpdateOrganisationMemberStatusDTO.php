<?php

namespace App\DTOs\Organisation;

use App\Models\OrganisationMember;
use App\Models\User;

class UpdateOrganisationMemberStatusDTO
{
    public function __construct(
        public OrganisationMember $member,
        public string $new_status,
        public User $performer,
        public ?string $reason = null,
    ) {}
}
