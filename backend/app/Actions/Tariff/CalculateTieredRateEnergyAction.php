<?php

namespace App\Actions\Tariff;

use App\Models\TariffStructure;

class CalculateTieredRateEnergyAction
{
    /**
     * Calculate energy (kWh) from cost for tiered rate tariff.
     *
     * @return array{kwh: float, breakdown: array}
     */
    public function execute(float $amount, TariffStructure $tariffStructure): array
    {
        $tiers = $tariffStructure->tariffTiers->sortBy('order');
        $estimatedKwh = 0.0;
        $remainingAmount = $amount;
        $breakdown = [];

        foreach ($tiers as $tier) {
            if ($remainingAmount <= 0) {
                break;
            }

            $rate = (float) $tier->rate_per_kwh;

            // Handle free tiers or invalid rates
            if ($rate <= 0) {
                if ($tier->max_kwh !== null) {
                    $tierCapacity = $tier->max_kwh - $tier->min_kwh;
                    $estimatedKwh += $tierCapacity;
                    $breakdown[] = [
                        'name' => $tier->name,
                        'rate' => 0.0,
                        'cost' => 0.0,
                        'kwh' => round($tierCapacity, 2),
                    ];

                    continue;
                }

                // Skip infinite free tiers to avoid infinite loop/value
                continue;
            }

            // Determine capacity of this tier in kWh
            $tierCapacityKwh = null;
            if ($tier->max_kwh !== null) {
                $tierCapacityKwh = $tier->max_kwh - $tier->min_kwh;
            }

            // Calculate the maximum cost this tier can absorb
            $maxTierCost = $tierCapacityKwh !== null
                ? $tierCapacityKwh * $rate
                : PHP_FLOAT_MAX;

            // Determine how much cost we allocate to this tier
            $costAllocated = min($remainingAmount, $maxTierCost);

            // Calculate kWh for this allocated cost
            $kwhInTier = $costAllocated / $rate;

            $estimatedKwh += $kwhInTier;
            $remainingAmount -= $costAllocated;

            $breakdown[] = [
                'name' => $tier->name,
                'rate' => $rate,
                'cost' => round($costAllocated, 2),
                'kwh' => round($kwhInTier, 2),
            ];
        }

        return ['kwh' => $estimatedKwh, 'breakdown' => $breakdown];
    }
}
