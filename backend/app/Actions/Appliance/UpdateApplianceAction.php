<?php

namespace App\Actions\Appliance;

use App\Models\Appliance;

class UpdateApplianceAction
{
    public function execute(Appliance $appliance, array $data): Appliance
    {
        $appliance->update(array_filter([
            'name' => $data['name'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'default_wattage' => $data['default_wattage'] ?? null,
            'default_usage_hours' => $data['default_usage_hours'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'is_public' => $data['is_public'] ?? null,
        ], fn ($value) => $value !== null));

        return $appliance->fresh(['category']);
    }
}
