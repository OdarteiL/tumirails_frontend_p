<?php

namespace App\Actions\Tariff;

use App\Models\TariffStructure;
use Carbon\Carbon;

class GetActiveTariffForDateAction
{
    /**
     * Get the active tariff structure for a specific date.
     */
    public function execute(Carbon $date, ?int $countryId = null): ?TariffStructure
    {
        $query = TariffStructure::with('tariffTiers')
            ->where('is_active', true)
            ->where('effective_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            })
            ->orderBy('effective_date', 'desc');

        if ($countryId) {
            $query->where('country_id', $countryId);
        }

        return $query->first();
    }
}
