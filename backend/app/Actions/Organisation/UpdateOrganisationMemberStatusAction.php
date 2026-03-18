<?php

namespace App\Actions\Organisation;

use App\Actions\Audit\LogAuditAction;
use App\DTOs\Audit\LogAuditDTO;
use App\DTOs\Organisation\UpdateOrganisationMemberStatusDTO;
use App\Events\OrganisationMemberStatusChangedEvent;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\User\InvalidStatusTransitionException;
use App\Models\AuditLog;
use App\Models\OrganisationMember;
use Illuminate\Support\Facades\DB;

class UpdateOrganisationMemberStatusAction
{
    public function __construct(
        protected LogAuditAction $logAuditAction
    ) {}

    /**
     * Update an organisation member's status (active/suspended).
     *
     * @throws InvalidStatusTransitionException
     * @throws UnauthorizedException
     */
    public function execute(UpdateOrganisationMemberStatusDTO $dto): OrganisationMember
    {
        $performer = $dto->performer;
        $memberToUpdate = $dto->member;

        // Authorization: Must be a System Admin OR the Owner of the specific organisation
        $isSystemAdmin = $performer->role === 'admin';
        $isOrgOwner = $memberToUpdate->organisation->owner()?->id === $performer->id;

        if (! $isSystemAdmin && ! $isOrgOwner) {
            throw new UnauthorizedException('Only administrators or the organisation owner can change a member\'s status.');
        }

        // Must be a valid status
        if (! in_array($dto->new_status, ['active', 'suspended'])) {
            throw new \InvalidArgumentException('Invalid member status provided.');
        }

        $oldStatus = $memberToUpdate->status;

        // Must be a meaningful transition
        if ($oldStatus === $dto->new_status) {
            throw new InvalidStatusTransitionException("Member is already {$dto->new_status}.");
        }

        return DB::transaction(function () use ($dto, $memberToUpdate, $oldStatus, $performer) {

            $memberToUpdate->status = $dto->new_status;
            $memberToUpdate->save();

            // Create Audit Log
            $logDto = new LogAuditDTO(
                user: $performer,
                auditable: $memberToUpdate,
                action: AuditLog::ACTION_STATUS_CHANGED,
                old_values: ['status' => $oldStatus],
                new_values: ['status' => $memberToUpdate->status],
                reason: $dto->reason
            );

            $this->logAuditAction->execute($logDto);

            // Dispatch Event
            event(new OrganisationMemberStatusChangedEvent($memberToUpdate, $oldStatus, $memberToUpdate->status, $performer));

            return $memberToUpdate;
        });
    }
}
