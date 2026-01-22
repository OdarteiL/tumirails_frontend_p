<?php

namespace App\Actions\Tariff;

use App\Models\TariffStructure;

class CalculateFlatRateEnergyAction
{
    /**
     * Calculate energy (kWh) from cost for flat rate tariff.
     *
     * @return array{kwh: float, breakdown: array}
     */
    public function execute(float $amount, TariffStructure $tariffStructure): array
    {
        $tier = $tariffStructure->tariffTiers->sortBy('order')->first();
        $estimatedKwh = 0.0;
        $breakdown = [];

        if ($tier && $tier->rate_per_kwh > 0) {
            $estimatedKwh = $amount / $tier->rate_per_kwh;

            $breakdown[] = [
                'name' => $tier->name ?? 'Flat Rate',
                'rate' => (float) $tier->rate_per_kwh,
                'cost' => round($amount, 2),
                'kwh' => round($estimatedKwh, 2),
            ];
        }

        return ['kwh' => $estimatedKwh, 'breakdown' => $breakdown];
    }
}
