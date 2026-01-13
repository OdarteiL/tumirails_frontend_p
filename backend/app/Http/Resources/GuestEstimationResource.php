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
            'total_watts' => $this->resource['total_watts'],
            'daily_kwh' => $this->resource['daily_kwh'],
            'monthly_kwh' => $this->resource['monthly_kwh'],
            'adjusted_monthly_kwh' => $this->resource['adjusted_monthly_kwh'],
            'estimated_daily_cost' => $this->resource['estimated_daily_cost'],
            'estimated_monthly_cost' => $this->resource['estimated_monthly_cost'],
            'power_factor_applied' => $this->resource['power_factor_applied'],
            'seasonal_multiplier' => $this->resource['seasonal_multiplier'],
            'location_multiplier' => $this->resource['location_multiplier'],
            'appliances_breakdown' => $this->resource['appliances_breakdown'],
            'calculation_metadata' => $this->resource['calculation_metadata'],
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
