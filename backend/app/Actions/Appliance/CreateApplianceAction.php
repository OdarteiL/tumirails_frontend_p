<?php

namespace App\Actions\Appliance;

use App\Models\Appliance;

class CreateApplianceAction
{
    public function execute(
        int $ownerId,
        string $ownerType,
        array $data,
        bool $isPublic = false
    ): Appliance {
        return Appliance::create([
            'owner_id' => $ownerId,
            'owner_type' => $ownerType,
            'name' => $data['name'],
            'category_id' => $data['category_id'],
            'default_wattage' => $data['default_wattage'],
            'default_usage_hours' => $data['default_usage_hours'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'is_public' => $isPublic,
            'is_active' => true,
        ]);
    }
}
