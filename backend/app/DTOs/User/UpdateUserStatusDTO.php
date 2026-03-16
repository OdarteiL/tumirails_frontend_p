<?php

namespace App\DTOs\User;

use App\Models\User;

class UpdateUserStatusDTO
{
    public function __construct(
        public User $user,
        public string $new_status,
        public User $admin,
        public ?string $reason = null,
    ) {}
}
