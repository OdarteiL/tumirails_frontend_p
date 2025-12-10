<?php

namespace App\Actions\Organisation;

use App\Models\OrganisationInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RejectOrganisationInvitationAction
{
    public function execute(string $token, User $user): OrganisationInvitation
    {
        return DB::transaction(function () use ($token, $user) {
            $invitation = OrganisationInvitation::where('token', $token)
                ->where('email', $user->email)
                ->firstOrFail();

            if ($invitation->isExpired()) {
                throw new \Exception('This invitation is no longer valid.');
            }

            if ($invitation->accepted_at) {
                throw new \Exception('This invitation has already been accepted.');
            }

            if ($invitation->rejected_at) {
                throw new \Exception('This invitation has already been rejected.');
            }

            // Mark invitation as rejected
            $invitation->update(['rejected_at' => now()]);

            return $invitation->load('organisation');
        });
    }
}
