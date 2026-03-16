<?php

namespace App\DTOs\Audit;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LogAuditDTO
{
    public function __construct(
        public User $user,
        public Model $auditable,
        public string $action,
        public ?array $old_values = null,
        public ?array $new_values = null,
        public ?string $reason = null,
    ) {}
}
