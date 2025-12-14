<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstimationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'site' => [
                'id' => $this->site->id,
                'name' => $this->site->name,
            ],
            'owner' => [
                'id' => $this->owner->id,
                'type' => class_basename($this->owner_type),
                'name' => $this->getOwnerName(),
            ],
            'version' => $this->version,
            'previous_estimation_id' => $this->previous_estimation_id,
            'total_watts' => number_format($this->total_watts, 2, '.', ''),
            'daily_kwh' => number_format($this->daily_kwh, 2, '.', ''),
            'monthly_kwh' => number_format($this->monthly_kwh, 2, '.', ''),
            'estimated_monthly_cost' => number_format($this->estimated_monthly_cost, 2, '.', ''),
            'tariff_structure' => [
                'id' => $this->tariffStructure?->id,
                'name' => $this->tariffStructure?->name,
                'type' => $this->tariffStructure?->type,
            ],
            'power_factor_applied' => $this->power_factor_applied ? number_format($this->power_factor_applied, 2, '.', '') : null,
            'seasonal_multiplier' => $this->seasonal_multiplier ? number_format($this->seasonal_multiplier, 2, '.', '') : null,
            'appliances_breakdown' => $this->appliances_snapshot ?? [],
            'calculation_metadata' => $this->calculation_metadata ?? [],
            'created_by' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->full_name,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Get the owner's display name.
     */
    protected function getOwnerName(): string
    {
        if ($this->owner instanceof \App\Models\User) {
            return $this->owner->full_name;
        }

        if ($this->owner instanceof \App\Models\Organisation) {
            return $this->owner->name;
        }

        return 'Unknown';
    }
}
