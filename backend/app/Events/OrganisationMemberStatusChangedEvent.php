<?php

namespace App\Events;

use App\Models\OrganisationMember;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrganisationMemberStatusChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public OrganisationMember $member,
        public string $oldStatus,
        public string $newStatus,
        public User $performer,
    ) {}
}
