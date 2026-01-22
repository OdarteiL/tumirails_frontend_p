<?php

namespace App\Actions\Tariff;

use App\Models\TariffStructure;

class CalculateTariffCostAction
{
    public function __construct(
        protected CalculateFlatRateCostAction $calculateFlatRateCostAction,
        protected CalculateTieredRateCostAction $calculateTieredRateCostAction
    ) {}

    /**
     * Calculate cost based on tariff structure type.
     */
    public function execute(float $kwh, TariffStructure $tariffStructure): float
    {
        // Ensure tiers are loaded if not already
        if (! $tariffStructure->relationLoaded('tariffTiers')) {
            $tariffStructure->load('tariffTiers');
        }

        if ($tariffStructure->type === 'flat') {
            return $this->calculateFlatRateCostAction->execute($kwh, $tariffStructure);
        }

        return $this->calculateTieredRateCostAction->execute($kwh, $tariffStructure);
    }
}
