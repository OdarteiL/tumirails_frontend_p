<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteApplianceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'added_by_id' => $this->added_by_id,
            'added_by_type' => $this->added_by_type,
            'site_id' => $this->site_id,
            'appliance_id' => $this->appliance_id,
            'appliance' => [
                'id' => $this->appliance->id,
                'name' => $this->appliance->name,
                'default_wattage' => $this->appliance->default_wattage,
            ],
            'quantity' => $this->quantity,
            'daily_usage_hours' => $this->daily_usage_hours,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
