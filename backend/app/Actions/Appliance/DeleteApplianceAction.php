<?php

namespace App\Actions\Appliance;

use App\Models\Appliance;

class DeleteApplianceAction
{
    public function execute(Appliance $appliance): bool
    {
        return $appliance->update(['is_active' => false]);
    }
}
