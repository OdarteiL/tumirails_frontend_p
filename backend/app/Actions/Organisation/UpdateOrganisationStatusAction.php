<?php

namespace App\Actions\Organisation;

use App\Actions\Audit\LogAuditAction;
use App\DTOs\Audit\LogAuditDTO;
use App\DTOs\Organisation\UpdateOrganisationStatusDTO;
use App\Events\OrganisationStatusChangedEvent;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\User\InvalidStatusTransitionException;
use App\Models\AuditLog;
use App\Models\Organisation;
use Illuminate\Support\Facades\DB;

class UpdateOrganisationStatusAction
{
    public function __construct(
        protected LogAuditAction $logAuditAction
    ) {}

    /**
     * Update organisation status (active/suspended) with automatic audit
     * logging and validation of status transitions.
     *
     * @throws InvalidStatusTransitionException
     * @throws UnauthorizedException
     */
    public function execute(UpdateOrganisationStatusDTO $dto): Organisation
    {
        // Must be an admin
        if ($dto->admin->role !== 'admin') {
            throw new UnauthorizedException('Only administrators can change organisation status.');
        }

        // Must be a valid status
        if (! in_array($dto->new_status, ['active', 'suspended'])) {
            throw new \InvalidArgumentException('Invalid organisation status provided.');
        }

        $organisation = $dto->organisation;
        $oldStatus = $organisation->status;

        // Must be a meaningful transition
        if ($oldStatus === $dto->new_status) {
            throw new InvalidStatusTransitionException("Organisation is already {$dto->new_status}.");
        }

        return DB::transaction(function () use ($dto, $organisation, $oldStatus) {

            $organisation->status = $dto->new_status;
            $organisation->save();

            // Create Audit Log using the LogAuditAction polymorphic action
            $logDto = new LogAuditDTO(
                user: $dto->admin,
                auditable: $organisation,
                action: AuditLog::ACTION_STATUS_CHANGED,
                old_values: ['status' => $oldStatus],
                new_values: ['status' => $organisation->status],
                reason: $dto->reason
            );

            $this->logAuditAction->execute($logDto);

            // Dispatch Event
            event(new OrganisationStatusChangedEvent($organisation, $oldStatus, $organisation->status, $dto->admin));

            return $organisation;
        });
    }
}
