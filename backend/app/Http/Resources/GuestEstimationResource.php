<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuestEstimationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'reference_code' => $this->when($this->reference_code, $this->reference_code),
            'total_watts' => $this->resource['total_watts'] ?? $this->total_watts,
            'daily_kwh' => $this->resource['daily_kwh'] ?? $this->daily_kwh,
            'monthly_kwh' => $this->resource['monthly_kwh'] ?? $this->monthly_kwh,
            'adjusted_monthly_kwh' => $this->resource['adjusted_monthly_kwh'] ?? null,
            'estimated_daily_cost' => $this->resource['estimated_daily_cost'] ?? null,
            'estimated_monthly_cost' => $this->resource['estimated_monthly_cost'] ?? $this->estimated_monthly_cost,
            'power_factor_applied' => $this->resource['power_factor_applied'] ?? ($this->calculation_metadata['power_factor_applied'] ?? null),
            'seasonal_multiplier' => $this->resource['seasonal_multiplier'] ?? ($this->calculation_metadata['seasonal_multiplier'] ?? null),
            'location_multiplier' => $this->resource['location_multiplier'] ?? null,
            'appliances_breakdown' => $this->resource['appliances_breakdown'] ?? $this->appliances_snapshot,
            'calculation_metadata' => $this->resource['calculation_metadata'] ?? $this->calculation_metadata,
        ];
    }

    /**
     * Customize the response wrapper.
     */
    public function with(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Estimation calculated successfully',
        ];
    }
}
