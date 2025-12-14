<?php

namespace App\Actions\Estimation;

use App\Models\LocationMultiplier;
use App\Models\SeasonalAdjustment;
use App\Models\Site;
use App\Models\TariffStructure;

class CalculateEstimationAction
{
    /**
     * Execute the estimation calculation.
     *
     * @param Site $site
     * @param TariffStructure $tariffStructure
     * @param SeasonalAdjustment|null $seasonalAdjustment
     * @param LocationMultiplier|null $locationMultiplier
     * @return array Calculation results with breakdown
     */
    public function execute(
        Site $site,
        TariffStructure $tariffStructure,
        ?SeasonalAdjustment $seasonalAdjustment = null,
        ?LocationMultiplier $locationMultiplier = null
    ): array {
        // Load site appliances with their appliance and category data
        $siteAppliances = $site->siteAppliances()
            ->with(['appliance.category'])
            ->get();

        // Handle edge case: no appliances
        if ($siteAppliances->isEmpty()) {
            return $this->emptyEstimation($tariffStructure, $seasonalAdjustment, $locationMultiplier);
        }

        // Calculate breakdown per appliance
        $appliancesBreakdown = [];
        $totalWatts = 0;
        $dailyKwh = 0;

        foreach ($siteAppliances as $siteAppliance) {
            $appliance = $siteAppliance->appliance;
            $category = $appliance->category;
            
            // Get power factor from category
            $powerFactor = $category->power_factor ?? 0.90;
            
            // Calculate watts for this appliance
            $watts = $appliance->default_wattage * $siteAppliance->quantity;
            $totalWatts += $watts;
            
            // Calculate daily kWh: (watts * quantity * hours * power_factor) / 1000
            $applianceDailyKwh = ($appliance->default_wattage * $siteAppliance->quantity * $siteAppliance->daily_usage_hours * $powerFactor) / 1000;
            $dailyKwh += $applianceDailyKwh;
            
            $appliancesBreakdown[] = [
                'id' => $appliance->id,
                'name' => $appliance->name,
                'category' => $category->name,
                'watts' => (float) $appliance->default_wattage,
                'quantity' => $siteAppliance->quantity,
                'daily_usage_hours' => (float) $siteAppliance->daily_usage_hours,
                'power_factor' => (float) $powerFactor,
                'daily_kwh' => round($applianceDailyKwh, 2),
            ];
        }

        // Calculate monthly kWh
        $monthlyKwh = $dailyKwh * 30;

        // Apply seasonal multiplier
        $seasonalMultiplier = $seasonalAdjustment?->multiplier ?? 1.0;
        $adjustedMonthlyKwh = $monthlyKwh * $seasonalMultiplier;

        // Apply location multiplier
        $locationMultiplierValue = $locationMultiplier?->multiplier ?? 1.0;
        $finalMonthlyKwh = $adjustedMonthlyKwh * $locationMultiplierValue;

        // Calculate cost using tiered pricing
        $cost = $this->calculateTieredCost($finalMonthlyKwh, $tariffStructure);

        // Calculate cost per appliance for breakdown
        foreach ($appliancesBreakdown as &$breakdown) {
            $applianceMonthlyKwh = $breakdown['daily_kwh'] * 30 * $seasonalMultiplier * $locationMultiplierValue;
            $breakdown['monthly_cost'] = round(
                ($applianceMonthlyKwh / $finalMonthlyKwh) * $cost,
                2
            );
        }

        return [
            'total_watts' => round($totalWatts, 2),
            'daily_kwh' => round($dailyKwh, 2),
            'monthly_kwh' => round($monthlyKwh, 2),
            'adjusted_monthly_kwh' => round($finalMonthlyKwh, 2),
            'estimated_monthly_cost' => round($cost, 2),
            'power_factor_applied' => $this->getAveragePowerFactor($siteAppliances),
            'seasonal_multiplier' => (float) $seasonalMultiplier,
            'location_multiplier' => (float) $locationMultiplierValue,
            'appliances_breakdown' => $appliancesBreakdown,
            'calculation_metadata' => [
                'tariff_structure_id' => $tariffStructure->id,
                'tariff_structure_name' => $tariffStructure->name,
                'tariff_type' => $tariffStructure->type,
                'seasonal_adjustment_id' => $seasonalAdjustment?->id,
                'seasonal_adjustment_name' => $seasonalAdjustment?->season_name,
                'location_multiplier_id' => $locationMultiplier?->id,
                'location_region' => $locationMultiplier?->region,
                'location_city' => $locationMultiplier?->city,
                'calculated_at' => now()->toIso8601String(),
                'appliance_count' => $siteAppliances->count(),
            ],
        ];
    }

    /**
     * Calculate cost using tiered pricing structure.
     *
     * @param float $kwh
     * @param TariffStructure $tariffStructure
     * @return float
     */
    protected function calculateTieredCost(float $kwh, TariffStructure $tariffStructure): float
    {
        // For flat rate, use first tier's rate
        if ($tariffStructure->type === 'flat') {
            $firstTier = $tariffStructure->tariffTiers()->ordered()->first();
            return $firstTier ? $kwh * $firstTier->rate_per_kwh : 0;
        }

        // For tiered pricing, calculate cost per tier
        $tiers = $tariffStructure->tariffTiers()->ordered()->get();
        $totalCost = 0;
        $remainingKwh = $kwh;

        foreach ($tiers as $tier) {
            if ($remainingKwh <= 0) {
                break;
            }

            // Calculate kWh in this tier
            $tierKwh = 0;
            
            if ($tier->appliesTo($kwh)) {
                if ($tier->max_kwh === null) {
                    // Unlimited tier - use all remaining
                    $tierKwh = $remainingKwh;
                } else {
                    // Calculate usage in this tier range
                    $tierMin = $tier->min_kwh;
                    $tierMax = $tier->max_kwh;
                    
                    if ($kwh <= $tierMax) {
                        // Usage ends in this tier
                        $tierKwh = $kwh - $tierMin;
                    } else {
                        // Usage spans this tier completely
                        $tierKwh = $tierMax - $tierMin;
                    }
                }
                
                $totalCost += $tierKwh * $tier->rate_per_kwh;
                $remainingKwh -= $tierKwh;
            }
        }

        return $totalCost;
    }

    /**
     * Calculate average power factor across all appliances.
     *
     * @param \Illuminate\Support\Collection $siteAppliances
     * @return float
     */
    protected function getAveragePowerFactor($siteAppliances): float
    {
        if ($siteAppliances->isEmpty()) {
            return 0.90;
        }

        $totalPowerFactor = 0;
        $count = 0;

        foreach ($siteAppliances as $siteAppliance) {
            $powerFactor = $siteAppliance->appliance->category->power_factor ?? 0.90;
            $totalPowerFactor += $powerFactor;
            $count++;
        }

        return round($totalPowerFactor / $count, 2);
    }

    /**
     * Return empty estimation for sites with no appliances.
     *
     * @param TariffStructure $tariffStructure
     * @param SeasonalAdjustment|null $seasonalAdjustment
     * @param LocationMultiplier|null $locationMultiplier
     * @return array
     */
    protected function emptyEstimation(
        TariffStructure $tariffStructure,
        ?SeasonalAdjustment $seasonalAdjustment,
        ?LocationMultiplier $locationMultiplier
    ): array {
        return [
            'total_watts' => 0,
            'daily_kwh' => 0,
            'monthly_kwh' => 0,
            'adjusted_monthly_kwh' => 0,
            'estimated_monthly_cost' => 0,
            'power_factor_applied' => 0.90,
            'seasonal_multiplier' => (float) ($seasonalAdjustment?->multiplier ?? 1.0),
            'location_multiplier' => (float) ($locationMultiplier?->multiplier ?? 1.0),
            'appliances_breakdown' => [],
            'calculation_metadata' => [
                'tariff_structure_id' => $tariffStructure->id,
                'tariff_structure_name' => $tariffStructure->name,
                'tariff_type' => $tariffStructure->type,
                'seasonal_adjustment_id' => $seasonalAdjustment?->id,
                'seasonal_adjustment_name' => $seasonalAdjustment?->season_name,
                'location_multiplier_id' => $locationMultiplier?->id,
                'location_region' => $locationMultiplier?->region,
                'location_city' => $locationMultiplier?->city,
                'calculated_at' => now()->toIso8601String(),
                'appliance_count' => 0,
            ],
        ];
    }
}
