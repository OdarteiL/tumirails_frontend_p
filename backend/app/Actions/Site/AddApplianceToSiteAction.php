<?php

namespace App\Actions\Site;

use App\Models\UserAppliance;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;

class AddApplianceToSiteAction
{
    public function execute(int $userId, int $siteId, int $applianceId, int $quantity, float $dailyUsageHours, ?string $notes = null): UserAppliance
    {
        try {
            return UserAppliance::create([
                'user_id' => $userId,
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
