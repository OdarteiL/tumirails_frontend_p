<?php

namespace App\Actions\Site;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Collection;

class GetOrganisationSitesAction
{
    public function execute(Organisation $organisation): Collection
    {
        return $organisation->sites()->latest()->get();
    }
}
