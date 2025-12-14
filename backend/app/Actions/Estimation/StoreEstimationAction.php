<?php

namespace App\Actions\Estimation;

use App\Models\Estimation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StoreEstimationAction
{
    /**
     * Store or update an estimation with versioning logic.
     *
     * @param User|Model $owner The owner of the estimation (User or Organisation)
     * @param Site $site
     * @param array $calculationResults Results from CalculateEstimationAction
     * @param User $createdBy
     * @return Estimation
     */
    public function execute(
        Model $owner,
        Site $site,
        array $calculationResults,
        User $createdBy
    ): Estimation {
        return DB::transaction(function () use ($owner, $site, $calculationResults, $createdBy) {
            // Check if estimation already exists for this site+owner combination
            $existingEstimation = Estimation::where('owner_id', $owner->id)
                ->where('owner_type', get_class($owner))
                ->where('site_id', $site->id)
                ->orderBy('version', 'desc')
                ->first();

            // Prepare appliances snapshot
            $appliancesSnapshot = $calculationResults['appliances_breakdown'];

            // Determine if we need a new version
            $needsNewVersion = false;
            
            if ($existingEstimation) {
                // Compare appliances snapshot to detect changes
                $needsNewVersion = $this->appliancesChanged(
                    $existingEstimation->appliances_snapshot,
                    $appliancesSnapshot
                );
            }

            if (!$existingEstimation || $needsNewVersion) {
                // Create new estimation (or new version)
                $version = $existingEstimation ? $existingEstimation->version + 1 : 1;
                
                return Estimation::create([
                    'owner_id' => $owner->id,
                    'owner_type' => get_class($owner),
                    'site_id' => $site->id,
                    'version' => $version,
                    'previous_estimation_id' => $existingEstimation?->id,
                    'total_watts' => $calculationResults['total_watts'],
                    'daily_kwh' => $calculationResults['daily_kwh'],
                    'monthly_kwh' => $calculationResults['adjusted_monthly_kwh'],
                    'estimated_monthly_cost' => $calculationResults['estimated_monthly_cost'],
                    'tariff_structure_id' => $calculationResults['calculation_metadata']['tariff_structure_id'],
                    'power_factor_applied' => $calculationResults['power_factor_applied'],
                    'seasonal_multiplier' => $calculationResults['seasonal_multiplier'],
                    'appliances_snapshot' => $appliancesSnapshot,
                    'calculation_metadata' => $calculationResults['calculation_metadata'],
                    'created_by' => $createdBy->id,
                ]);
            } else {
                // Update existing estimation in place (appliances unchanged, just recalculating)
                $existingEstimation->update([
                    'total_watts' => $calculationResults['total_watts'],
                    'daily_kwh' => $calculationResults['daily_kwh'],
                    'monthly_kwh' => $calculationResults['adjusted_monthly_kwh'],
                    'estimated_monthly_cost' => $calculationResults['estimated_monthly_cost'],
                    'tariff_structure_id' => $calculationResults['calculation_metadata']['tariff_structure_id'],
                    'power_factor_applied' => $calculationResults['power_factor_applied'],
                    'seasonal_multiplier' => $calculationResults['seasonal_multiplier'],
                    'appliances_snapshot' => $appliancesSnapshot,
                    'calculation_metadata' => $calculationResults['calculation_metadata'],
                ]);

                return $existingEstimation->fresh();
            }
        });
    }

    /**
     * Check if appliances have changed by comparing snapshots.
     *
     * @param array $oldSnapshot
     * @param array $newSnapshot
     * @return bool
     */
    protected function appliancesChanged(array $oldSnapshot, array $newSnapshot): bool
    {
        // If counts differ, appliances changed
        if (count($oldSnapshot) !== count($newSnapshot)) {
            return true;
        }

        // Create comparable arrays (sorted by appliance id)
        $oldAppliances = collect($oldSnapshot)->sortBy('id')->values()->all();
        $newAppliances = collect($newSnapshot)->sortBy('id')->values()->all();

        // Compare each appliance
        foreach ($oldAppliances as $index => $oldAppliance) {
            $newAppliance = $newAppliances[$index] ?? null;

            if (!$newAppliance) {
                return true;
            }

            // Check critical fields that indicate appliance changes
            if (
                $oldAppliance['id'] !== $newAppliance['id'] ||
                $oldAppliance['quantity'] !== $newAppliance['quantity'] ||
                $oldAppliance['daily_usage_hours'] !== $newAppliance['daily_usage_hours']
            ) {
                return true;
            }
        }

        return false;
    }
}
