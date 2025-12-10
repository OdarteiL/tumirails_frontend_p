<?php

namespace App\Services;

use App\Actions\Site\AddApplianceToSiteAction;
use App\Models\Organisation;
use App\Models\Site;
use App\Models\SiteAppliance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SiteApplianceService
{
    public function __construct(
        private AddApplianceToSiteAction $addApplianceAction
    ) {}

    /**
     * Add an appliance to a site (for user-owned sites).
     */
    public function addAppliance(int $userId, int $siteId, int $applianceId, int $quantity, float $dailyUsageHours, ?string $notes = null): SiteAppliance
    {
        $site = Site::where('id', $siteId)
            ->where('owner_id', $userId)
            ->where('owner_type', User::class)
            ->firstOrFail();

        return DB::transaction(function () use ($userId, $siteId, $applianceId, $quantity, $dailyUsageHours, $notes) {
            return $this->addApplianceAction->execute(
                $userId,
                User::class,
                $siteId,
                $applianceId,
                $quantity,
                $dailyUsageHours,
                $notes
            );
        });
    }

    /**
     * Add an appliance to an organisation site.
     */
    public function addApplianceToOrganisationSite(
        int $userId,
        int $organisationId,
        int $siteId,
        int $applianceId,
        int $quantity,
        float $dailyUsageHours,
        ?string $notes = null
    ): SiteAppliance {
        // Verify site belongs to organisation
        $site = Site::where('id', $siteId)
            ->where('owner_id', $organisationId)
            ->where('owner_type', Organisation::class)
            ->firstOrFail();

        return DB::transaction(function () use ($userId, $siteId, $applianceId, $quantity, $dailyUsageHours, $notes) {
            return $this->addApplianceAction->execute(
                $userId,
                User::class, // The user who added it (organisation member)
                $siteId,
                $applianceId,
                $quantity,
                $dailyUsageHours,
                $notes
            );
        });
    }
}
