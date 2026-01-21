<?php

namespace App\Services;

use App\Actions\Tariff\GetActiveTariffForDateAction;
use App\Actions\Tariff\GetLatestActiveTariffAction;
use App\Models\Country;
use App\Models\TariffStructure;
use Carbon\Carbon;

class TariffService
{
    public function __construct(
        protected GetLatestActiveTariffAction $getLatestActiveTariffAction,
        protected GetActiveTariffForDateAction $getActiveTariffForDateAction
    ) {}

    /**
     * Get the latest active tariff.
     */
    public function getLatestActiveTariff(?int $countryId = null): ?TariffStructure
    {
        $countryId = $countryId ?? $this->getDefaultCountryId();

        return $this->getLatestActiveTariffAction->execute($countryId);
    }

    /**
     * Get the active tariff for a specific date.
     */
    public function getActiveTariffForDate(Carbon $date, ?int $countryId = null): ?TariffStructure
    {
        $countryId = $countryId ?? $this->getDefaultCountryId();

        return $this->getActiveTariffForDateAction->execute($date, $countryId);
    }

    /**
     * Get the latest active tariff or throw an exception if not found.
     *
     * @throws \Exception
     */
    public function getLatestActiveTariffOrFail(?int $countryId = null): TariffStructure
    {
        $tariff = $this->getLatestActiveTariff($countryId);

        if (! $tariff) {
            throw new \Exception('No active tariff structure found.');
        }

        return $tariff;
    }

    protected function getDefaultCountryId(): ?int
    {
        return Country::where('is_active', true)->value('id');
    }
}
