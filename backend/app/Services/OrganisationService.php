<?php

namespace App\Services;

use App\Actions\Organisation\AcceptOrganisationInvitationAction;
use App\Actions\Organisation\CreateOrganisationAction;
use App\Actions\Organisation\InviteOrganisationMemberAction;
use App\Actions\Organisation\RejectOrganisationInvitationAction;
use App\Actions\Organisation\RemoveOrganisationMemberAction;
use App\Actions\Organisation\UpdateOrganisationAction;
use App\Actions\Organisation\UpdateOrganisationMemberAction;
use App\Models\Organisation;
use App\Models\OrganisationInvitation;
use App\Models\OrganisationMember;
use App\Models\User;

class OrganisationService
{
    public function __construct(
        private CreateOrganisationAction $createOrganisationAction,
        private UpdateOrganisationAction $updateOrganisationAction,
        private InviteOrganisationMemberAction $inviteOrganisationMemberAction,
        private AcceptOrganisationInvitationAction $acceptOrganisationInvitationAction,
        private RejectOrganisationInvitationAction $rejectOrganisationInvitationAction,
        private UpdateOrganisationMemberAction $updateOrganisationMemberAction,
        private RemoveOrganisationMemberAction $removeOrganisationMemberAction
    ) {}

    /**
     * Create a new organisation.
     */
    public function createOrganisation(array $data, User $user): Organisation
    {
        return $this->createOrganisationAction->execute($data, $user);
    }

    /**
     * Update an organisation.
     */
    public function updateOrganisation(Organisation $organisation, array $data): Organisation
    {
        return $this->updateOrganisationAction->execute($organisation, $data);
    }

    /**
     * Delete an organisation.
     */
    public function deleteOrganisation(Organisation $organisation): void
    {
        $organisation->delete();
    }

    /**
     * Invite a member to an organisation.
     */
    public function inviteMember(Organisation $organisation, array $data, User $inviter)
    {
        return $this->inviteOrganisationMemberAction->execute($organisation, $data, $inviter);
    }

    /**
     * Accept an organisation invitation.
     */
    public function acceptInvitation(string $token, User $user): OrganisationMember
    {
        return $this->acceptOrganisationInvitationAction->execute($token, $user);
    }

    /**
     * Reject an organisation invitation.
     */
    public function rejectInvitation(string $token, User $user): OrganisationInvitation
    {
        return $this->rejectOrganisationInvitationAction->execute($token, $user);
    }

    /**
     * Update a member's role.
     */
    public function updateMember(OrganisationMember $member, array $data): OrganisationMember
    {
        return $this->updateOrganisationMemberAction->execute($member, $data);
    }

    /**
     * Remove a member from an organisation.
     */
    public function removeMember(OrganisationMember $member): void
    {
        $this->removeOrganisationMemberAction->execute($member);
    }

    /**
     * Check if user has permission in organisation.
     */
    public function userHasPermission(User $user, Organisation $organisation, string $permission): bool
    {
        $membership = $organisation->members()
            ->where('user_id', $user->id)
            ->first();

        if (! $membership) {
            return false;
        }

        return match ($permission) {
            'owner' => $membership->role === 'owner',
            'admin' => in_array($membership->role, ['owner', 'admin']),
            'manage_members' => in_array($membership->role, ['owner', 'admin']),
            'edit' => in_array($membership->role, ['owner', 'admin']),
            'delete' => $membership->role === 'owner',
            default => false,
        };
    }
}
