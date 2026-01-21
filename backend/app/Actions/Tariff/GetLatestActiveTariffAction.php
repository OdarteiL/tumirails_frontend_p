<?php

namespace App\Actions\Tariff;

use App\Models\TariffStructure;

class GetLatestActiveTariffAction
{
    /**
     * Get the latest active tariff structure.
     */
    public function execute(?int $countryId = null): ?TariffStructure
    {
        $query = TariffStructure::with('tariffTiers')
            ->where('is_active', true)
            ->where('effective_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->orderBy('effective_date', 'desc');

        if ($countryId) {
            $query->where('country_id', $countryId);
        }

        return $query->first();
    }
}
