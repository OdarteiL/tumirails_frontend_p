<?php

namespace App\Services;

use App\Actions\User\UpdateUserStatusAction;
use App\DTOs\User\UpdateUserStatusDTO;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\User\InvalidStatusTransitionException;
use App\Models\User;

class UserAdministrationService
{
    public function __construct(
        protected UpdateUserStatusAction $updateUserStatusAction
    ) {}

    /**
     * Update a user's status.
     *
     * @throws UnauthorizedException
     * @throws InvalidStatusTransitionException
     */
    public function updateUserStatus(User $targetUser, string $newStatus, User $adminUser, ?string $reason = null): User
    {
        $dto = new UpdateUserStatusDTO(
            user: $targetUser,
            new_status: $newStatus,
            admin: $adminUser,
            reason: $reason
        );

        return $this->updateUserStatusAction->execute($dto);
    }
}
