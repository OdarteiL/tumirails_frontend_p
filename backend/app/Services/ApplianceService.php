<?php

namespace App\Services;

use App\Actions\Appliance\CreateApplianceAction;
use App\Actions\Appliance\DeleteApplianceAction;
use App\Actions\Appliance\GetAppliancesAction;
use App\Actions\Appliance\UpdateApplianceAction;
use App\Models\Appliance;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ApplianceService
{
    public function __construct(
        private GetAppliancesAction $getAppliancesAction,
        private CreateApplianceAction $createApplianceAction,
        private UpdateApplianceAction $updateApplianceAction,
        private DeleteApplianceAction $deleteApplianceAction
    ) {}

    /**
     * Get appliances visible to the user with optional filters.
     */
    public function getVisibleAppliances(
        int $userId,
        string $userType = User::class,
        ?int $categoryId = null,
        ?string $search = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->getAppliancesAction->execute($userId, $userType, $categoryId, $search, $perPage);
    }

    /**
     * Create a new appliance owned by the user.
     */
    public function createAppliance(
        int $ownerId,
        string $ownerType,
        array $data,
        bool $isPublic = false
    ): Appliance {
        return DB::transaction(function () use ($ownerId, $ownerType, $data, $isPublic) {
            return $this->createApplianceAction->execute($ownerId, $ownerType, $data, $isPublic);
        });
    }

    /**
     * Update an existing appliance.
     */
    public function updateAppliance(Appliance $appliance, array $data): Appliance
    {
        return DB::transaction(function () use ($appliance, $data) {
            return $this->updateApplianceAction->execute($appliance, $data);
        });
    }

    /**
     * Soft delete an appliance.
     */
    public function deleteAppliance(Appliance $appliance): bool
    {
        return DB::transaction(function () use ($appliance) {
            return $this->deleteApplianceAction->execute($appliance);
        });
    }

    /**
     * Check if user can view the appliance.
     */
    public function canView(Appliance $appliance, int $userId, string $userType = User::class): bool
    {
        return $appliance->is_public ||
            ($appliance->owner_id === $userId && $appliance->owner_type === $userType);
    }

    /**
     * Check if user owns the appliance.
     */
    public function isOwner(Appliance $appliance, int $userId, string $userType = User::class): bool
    {
        return $appliance->owner_id === $userId && $appliance->owner_type === $userType;
    }
}
