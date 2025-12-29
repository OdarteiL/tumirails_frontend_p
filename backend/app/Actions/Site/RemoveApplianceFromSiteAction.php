<?php

namespace App\Actions\Site;

use App\Models\SiteAppliance;

class RemoveApplianceFromSiteAction
{
    /**
     * Remove a site appliance by id, ensuring it belongs to the provided site.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function execute(int $siteApplianceId, int $siteId): bool
    {
        $siteAppliance = SiteAppliance::where('id', $siteApplianceId)
            ->where('site_id', $siteId)
            ->firstOrFail();

        return (bool) $siteAppliance->delete();
    }
}
