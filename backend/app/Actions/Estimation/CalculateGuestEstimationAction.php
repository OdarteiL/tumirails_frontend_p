<?php

namespace App\Actions\Estimation;

use App\Models\TariffStructure;
use Illuminate\Support\Facades\Log;

class CalculateGuestEstimationAction
{
    /**
     * Execute the guest estimation calculation.
     */
    public function execute(array $appliances): array
    {
        // For guests, we'll use the default active tariff structure.
        // A more robust solution might determine this based on user's location if provided.
        $tariffStructure = TariffStructure::where('is_active', true)->orderBy('effective_date', 'desc')->first();

        if (! $tariffStructure) {
            // It's crucial to handle the case where no active tariff structure is found.
            // This could be returning an error or a default (zero) estimation.
            Log::error('No active tariff structure found for guest estimation.');

            return $this->emptyEstimation();
        }

        $totalWatts = 0;
        $dailyKwh = 0;
        $appliancesBreakdown = [];
        $powerFactor = 0.90; // Default power factor for guest estimations

        foreach ($appliances as $appliance) {
            $watts = $appliance['wattage'] * $appliance['quantity'];
            $totalWatts += $watts;

            $applianceDailyKwh = ($appliance['wattage'] * $appliance['quantity'] * $appliance['daily_usage_hours'] * $powerFactor) / 1000;
            $dailyKwh += $applianceDailyKwh;

            $appliancesBreakdown[] = [
                'name' => $appliance['name'] ?? 'Appliance', // Generic name for guest appliances
                'wattage' => (float) $appliance['wattage'],
                'quantity' => (int) $appliance['quantity'],
                'daily_usage_hours' => (float) $appliance['daily_usage_hours'],
                'power_factor' => (float) $powerFactor,
                'daily_kwh' => round($applianceDailyKwh, 2),
            ];
        }

        $monthlyKwh = $dailyKwh * 30;
        $cost = $this->calculateTieredCost($monthlyKwh, $tariffStructure);

        foreach ($appliancesBreakdown as &$breakdown) {
            if ($monthlyKwh > 0) {
                $breakdown['monthly_cost'] = round(($breakdown['daily_kwh'] * 30 / $monthlyKwh) * $cost, 2);
            } else {
                $breakdown['monthly_cost'] = 0;
            }
        }

        return [
            'total_watts' => round($totalWatts, 2),
            'daily_kwh' => round($dailyKwh, 2),
            'monthly_kwh' => round($monthlyKwh, 2),
            'adjusted_monthly_kwh' => round($monthlyKwh, 2), // No adjustments for guests yet
            'estimated_daily_cost' => round($cost / 30, 2),
            'estimated_monthly_cost' => round($cost, 2),
            'power_factor_applied' => $powerFactor,
            'seasonal_multiplier' => 1.0,
            'location_multiplier' => 1.0,
            'appliances_breakdown' => $appliancesBreakdown,
            'calculation_metadata' => [
                'tariff_structure_id' => $tariffStructure->id,
                'tariff_structure_name' => $tariffStructure->name,
                'tariff_type' => $tariffStructure->type,
                'calculated_at' => now()->toIso8601String(),
                'appliance_count' => count($appliances),
            ],
        ];
    }

    protected function calculateTieredCost(float $kwh, TariffStructure $tariffStructure): float
    {
        if ($tariffStructure->type === 'flat') {
            $firstTier = $tariffStructure->tariffTiers()->ordered()->first();

            return $firstTier ? $kwh * $firstTier->rate_per_kwh : 0;
        }

        $tiers = $tariffStructure->tariffTiers()->ordered()->get();
        $totalCost = 0;
        $remainingKwh = $kwh;

        foreach ($tiers as $tier) {
            if ($remainingKwh <= 0) {
                break;
            }

            $tierKwh = 0;
            if ($tier->max_kwh === null) {
                $tierKwh = $remainingKwh;
            } else {
                $tierCapacity = $tier->max_kwh - $tier->min_kwh;
                $tierKwh = min($remainingKwh, $tierCapacity);
            }

            $totalCost += $tierKwh * $tier->rate_per_kwh;
            $remainingKwh -= $tierKwh;
        }

        return $totalCost;
    }

    protected function emptyEstimation(): array
    {
        return [
            'total_watts' => 0,
            'daily_kwh' => 0,
            'monthly_kwh' => 0,
            'adjusted_monthly_kwh' => 0,
            'estimated_daily_cost' => 0,
            'estimated_monthly_cost' => 0,
            'power_factor_applied' => 0,
            'seasonal_multiplier' => 1.0,
            'location_multiplier' => 1.0,
            'appliances_breakdown' => [],
            'calculation_metadata' => [
                'note' => 'No active tariff structure found.',
                'calculated_at' => now()->toIso8601String(),
            ],
        ];
    }
}
