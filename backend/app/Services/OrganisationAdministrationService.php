<?php

namespace App\Services;

use App\Actions\Organisation\UpdateOrganisationMemberStatusAction;
use App\Actions\Organisation\UpdateOrganisationStatusAction;
use App\DTOs\Organisation\UpdateOrganisationMemberStatusDTO;
use App\DTOs\Organisation\UpdateOrganisationStatusDTO;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\User\InvalidStatusTransitionException;
use App\Models\Organisation;
use App\Models\OrganisationMember;
use App\Models\User;

class OrganisationAdministrationService
{
    public function __construct(
        protected UpdateOrganisationStatusAction $updateOrganisationStatusAction,
        protected UpdateOrganisationMemberStatusAction $updateOrganisationMemberStatusAction
    ) {}

    /**
     * Update an organisation's status.
     *
     * @throws UnauthorizedException
     * @throws InvalidStatusTransitionException
     */
    public function updateOrganisationStatus(Organisation $organisation, string $newStatus, User $adminUser, ?string $reason = null): Organisation
    {
        $dto = new UpdateOrganisationStatusDTO(
            organisation: $organisation,
            new_status: $newStatus,
            admin: $adminUser,
            reason: $reason
        );

        return $this->updateOrganisationStatusAction->execute($dto);
    }

    /**
     * Update an organisation member's status.
     *
     * @throws UnauthorizedException
     * @throws InvalidStatusTransitionException
     */
    public function updateOrganisationMemberStatus(OrganisationMember $member, string $newStatus, User $performer, ?string $reason = null): OrganisationMember
    {
        $dto = new UpdateOrganisationMemberStatusDTO(
            member: $member,
            new_status: $newStatus,
            performer: $performer,
            reason: $reason
        );

        return $this->updateOrganisationMemberStatusAction->execute($dto);
    }
}
