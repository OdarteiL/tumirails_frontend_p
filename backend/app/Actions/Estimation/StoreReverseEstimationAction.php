<?php

namespace App\Actions\Estimation;

use App\Models\Estimation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StoreReverseEstimationAction
{
    /**
     * Store or update a reverse estimation with versioning logic.
     *
     * @param  Model  $owner  The owner of the estimation (User or Organisation)
     * @param  Site  $site  The site for the estimation
     * @param  array  $calculationResults  Results from ReverseEstimationAction
     * @param  User  $createdBy  The user creating the estimation
     */
    public function execute(
        Model $owner,
        Site $site,
        array $calculationResults,
        User $createdBy
    ): Estimation {
        return DB::transaction(function () use ($owner, $site, $calculationResults, $createdBy) {
            // Check if estimation already exists for this siteowner combination
            $existingEstimation = Estimation::where('owner_id', $owner->id)
                ->where('owner_type', get_class($owner))
                ->where('site_id', $site->id)
                ->orderBy('version', 'desc')
                ->first();

            // Determine if we need a new version based on amount change
            $needsNewVersion = false;

            if ($existingEstimation) {
                // If the cost basis changed significantly, create a new version
                if (abs($existingEstimation->estimated_monthly_cost - $calculationResults['amount']) > 0.01) {
                    $needsNewVersion = true;
                }
            }

            $commonData = [
                'total_watts' => 0, // Not applicable for reverse estimation
                'daily_kwh' => round($calculationResults['estimated_kwh'] / 30, 2), // Approximation
                'monthly_kwh' => $calculationResults['estimated_kwh'],
                'estimated_monthly_cost' => $calculationResults['amount'],
                'tariff_structure_id' => $calculationResults['metadata']['tariff_structure_id'] ?? null,
                'power_factor_applied' => 1.0,
                'seasonal_multiplier' => 1.0,
                'appliances_snapshot' => [], // No appliances in reverse estimation
                'calculation_metadata' => $calculationResults['metadata'],
            ];

            if (! $existingEstimation || $needsNewVersion) {
                $version = $existingEstimation ? $existingEstimation->version + 1 : 1;

                return Estimation::create(array_merge($commonData, [
                    'owner_id' => $owner->id,
                    'owner_type' => get_class($owner),
                    'site_id' => $site->id,
                    'version' => $version,
                    'previous_estimation_id' => $existingEstimation?->id,
                    'created_by' => $createdBy->id,
                ]));
            } else {
                // Update existing estimation in place
                $existingEstimation->update($commonData);

                return $existingEstimation->fresh();
            }
        });
    }
}
