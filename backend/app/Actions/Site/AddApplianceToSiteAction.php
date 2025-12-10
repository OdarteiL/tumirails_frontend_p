<?php

namespace App\Actions\Site;

use App\Models\SiteAppliance;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;

class AddApplianceToSiteAction
{
    public function execute(
        int $addedById,
        string $addedByType,
        int $siteId,
        int $applianceId,
        int $quantity,
        float $dailyUsageHours,
        ?string $notes = null
    ): SiteAppliance {
        try {
            return SiteAppliance::create([
                'added_by_id' => $addedById,
                'added_by_type' => $addedByType,
                'site_id' => $siteId,
                'appliance_id' => $applianceId,
                'quantity' => $quantity,
                'daily_usage_hours' => $dailyUsageHours,
                'notes' => $notes,
            ]);
        } catch (QueryException|UniqueConstraintViolationException $e) {
            if ($e instanceof UniqueConstraintViolationException ||
                (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062)) {
                throw new \InvalidArgumentException('Appliance already added to this site');
            }
            throw $e;
        }
    }
}
