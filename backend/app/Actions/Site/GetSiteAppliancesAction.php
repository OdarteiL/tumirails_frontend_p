<?php

namespace App\Actions\Site;

use App\Models\Site;
use Illuminate\Database\Eloquent\Collection;

class GetSiteAppliancesAction
{
    public function execute(Site $site): Collection
    {
        return $site->siteAppliances()->with(['appliance', 'appliance.category'])->latest()->get();
    }
}
