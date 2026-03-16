<?php

namespace App\DTOs\Organisation;

use App\Models\Organisation;
use App\Models\User;

class UpdateOrganisationStatusDTO
{
    public function __construct(
        public Organisation $organisation,
        public string $new_status,
        public User $admin,
        public ?string $reason = null,
    ) {}
}
