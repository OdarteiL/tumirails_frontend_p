<?php

namespace App\Actions\Estimation;

use App\Actions\Tariff\CalculateTariffCostAction;
use App\Services\TariffService;
use Illuminate\Support\Facades\Log;

class CalculateGuestEstimationAction
{
    protected TariffService $tariffService;

    protected CalculateTariffCostAction $calculateTariffCostAction;

    public function __construct(
        ?TariffService $tariffService = null,
        ?CalculateTariffCostAction $calculateTariffCostAction = null
    ) {
        $this->tariffService = $tariffService ?? app(TariffService::class);
        $this->calculateTariffCostAction = $calculateTariffCostAction ?? app(CalculateTariffCostAction::class);
    }

    /**
     * Execute the guest estimation calculation.
     */
    public function execute(array $appliances): array
    {
        // Guest estimation uses the default/latest tariff without country context
        $tariffStructure = $this->tariffService->getLatestActiveTariff();

        if (! $tariffStructure) {
            Log::error('No active tariff structure found for guest estimation.');

            return $this->emptyEstimation();
        }

        $totalWatts = 0;
        $dailyKwh = 0;
        $appliancesBreakdown = [];
        $powerFactor = 0.90;

        foreach ($appliances as $applianceData) {
            if (isset($applianceData['id'])) {
                $appliance = \App\Models\Appliance::where('id', $applianceData['id'])
                    ->where('is_public', true)
                    ->where('is_active', true)
                    ->first();

                if (! $appliance) {
                    continue;
                }

                $wattage = $applianceData['wattage'] ?? $appliance->default_wattage;
                $name = $appliance->name;
            } else {
                $wattage = $applianceData['wattage'];
                $name = $applianceData['name'] ?? 'Appliance';
            }

            $watts = $wattage * $applianceData['quantity'];
            $totalWatts += $watts;

            $applianceDailyKwh = ($wattage * $applianceData['quantity'] * $applianceData['daily_usage_hours'] * $powerFactor) / 1000;
            $dailyKwh += $applianceDailyKwh;

            $appliancesBreakdown[] = [
                'name' => $name,
                'wattage' => (float) $wattage,
                'quantity' => (int) $applianceData['quantity'],
                'daily_usage_hours' => (float) $applianceData['daily_usage_hours'],
                'power_factor' => (float) $powerFactor,
                'daily_kwh' => round($applianceDailyKwh, 2),
            ];
        }

        $monthlyKwh = $dailyKwh * 30;
        $cost = $this->calculateTariffCostAction->execute($monthlyKwh, $tariffStructure);

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
            'adjusted_monthly_kwh' => round($monthlyKwh, 2),
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
