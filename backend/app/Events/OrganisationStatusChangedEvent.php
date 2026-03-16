<?php

namespace App\Events;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrganisationStatusChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Organisation $organisation,
        public string $oldStatus,
        public string $newStatus,
        public User $admin,
    ) {}
}
