<?php

namespace App\Actions\Estimation;

use App\Actions\Tariff\CalculateFlatRateEnergyAction;
use App\Actions\Tariff\CalculateTieredRateEnergyAction;
use App\Services\TariffService;
use Carbon\Carbon;
use RuntimeException;

class CalculateEnergyFromCostAction
{
    public function __construct(
        protected TariffService $tariffService,
        protected CalculateFlatRateEnergyAction $calculateFlatRateEnergyAction,
        protected CalculateTieredRateEnergyAction $calculateTieredRateEnergyAction
    ) {}

    /**
     * Calculate estimated energy (kWh) from a monetary cost.
     *
     * @param  float  $amount  The monetary amount available.
     * @param  Carbon  $date  The date to use for tariff selection.
     * @return array Result containing estimated_kwh, effective_rate, and metadata.
     */
    public function execute(float $amount, Carbon $date): array
    {
        // Fetch the tariff structure for the given date
        $tariffStructure = $this->tariffService->getActiveTariffForDate($date);

        if (! $tariffStructure) {
            throw new RuntimeException("No active tariff structure found for date: {$date->toDateString()}");
        }

        // Ensure tiers are loaded
        if (! $tariffStructure->relationLoaded('tariffTiers')) {
            $tariffStructure->load('tariffTiers');
        }

        if ($tariffStructure->type === 'flat') {
            $result = $this->calculateFlatRateEnergyAction->execute($amount, $tariffStructure);
        } else {
            $result = $this->calculateTieredRateEnergyAction->execute($amount, $tariffStructure);
        }

        $estimatedKwh = $result['kwh'];
        $tiersBreakdown = $result['breakdown'];

        $effectiveRate = $estimatedKwh > 0 ? $amount / $estimatedKwh : 0;

        return [
            'estimated_kwh' => round($estimatedKwh, 2),
            'effective_rate' => round($effectiveRate, 4),
            'metadata' => [
                'tariff_structure_id' => $tariffStructure->id,
                'tariff_name' => $tariffStructure->name,
                'tariff_type' => $tariffStructure->type,
                'calculation_date' => $date->toIso8601String(),
                'tiers_breakdown' => $tiersBreakdown,
            ],
        ];
    }
}
