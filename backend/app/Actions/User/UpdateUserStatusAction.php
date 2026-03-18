<?php

namespace App\Actions\User;

use App\Actions\Audit\LogAuditAction;
use App\DTOs\Audit\LogAuditDTO;
use App\DTOs\User\UpdateUserStatusDTO;
use App\Events\UserStatusChangedEvent;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\User\InvalidStatusTransitionException;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateUserStatusAction
{
    public function __construct(
        protected LogAuditAction $logAuditAction
    ) {}

    /**
     * Update user status (active/suspended) with automatic audit
     * logging and validation of status transitions.
     *
     * @throws InvalidStatusTransitionException
     * @throws UnauthorizedException
     */
    public function execute(UpdateUserStatusDTO $dto): User
    {
        // Must be an admin
        if ($dto->admin->role !== 'admin') {
            throw new UnauthorizedException('Only administrators can change user an status.');
        }

        // Must be a valid status
        if (! in_array($dto->new_status, ['active', 'suspended'])) {
            throw new \InvalidArgumentException('Invalid user status provided.');
        }

        $user = $dto->user;
        $oldStatus = $user->status;

        // Must be a meaningful transition
        if ($oldStatus === $dto->new_status) {
            throw new InvalidStatusTransitionException("User is already {$dto->new_status}.");
        }

        return DB::transaction(function () use ($dto, $user, $oldStatus) {

            $user->status = $dto->new_status;
            $user->save();

            // Create Audit Log
            $logDto = new LogAuditDTO(
                user: $dto->admin,
                auditable: $user,
                action: AuditLog::ACTION_STATUS_CHANGED,
                old_values: ['status' => $oldStatus],
                new_values: ['status' => $user->status],
                reason: $dto->reason
            );

            $this->logAuditAction->execute($logDto);

            // Dispatch Event
            event(new UserStatusChangedEvent($user, $oldStatus, $user->status, $dto->admin));

            return $user;
        });
    }
}
