<?php

namespace App\Services;

use App\Actions\Site\AddApplianceToSiteAction;
use App\Models\Site;
use App\Models\UserAppliance;
use Illuminate\Support\Facades\DB;

class SiteApplianceService
{
    public function __construct(
        private AddApplianceToSiteAction $addApplianceAction
    ) {}

    public function addAppliance(int $userId, int $siteId, int $applianceId, int $quantity, float $dailyUsageHours, ?string $notes = null): UserAppliance
    {
        $site = Site::where('id', $siteId)
            ->where('user_id', $userId)
            ->firstOrFail();

        return DB::transaction(function () use ($userId, $siteId, $applianceId, $quantity, $dailyUsageHours, $notes) {
            return $this->addApplianceAction->execute($userId, $siteId, $applianceId, $quantity, $dailyUsageHours, $notes);
        });
    }
}
