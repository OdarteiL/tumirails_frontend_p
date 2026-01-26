<?php

namespace App\Actions\Estimation;

use App\Models\Estimation;
use Carbon\Carbon;
use Illuminate\Support\Str;

class StoreGuestReverseEstimationAction
{
    protected ReverseEstimationAction $reverseEstimationAction;

    public function __construct(
        ?ReverseEstimationAction $reverseEstimationAction = null
    ) {
        $this->reverseEstimationAction = $reverseEstimationAction ?? app(ReverseEstimationAction::class);
    }

    /**
     * Calculate and store a guest reverse estimation.
     */
    public function execute(array $data): Estimation
    {
        // Calculate
        $results = $this->reverseEstimationAction->execute($data);

        // Store
        return Estimation::create([
            'reference_code' => $this->generateReferenceCode(),
            'expires_at' => Carbon::now()->addDays(config('features.guest_estimation_ttl_days', 30)),
            'total_watts' => 0,
            'daily_kwh' => round($results['estimated_kwh'] / 30, 2),
            'monthly_kwh' => $results['estimated_kwh'],
            'estimated_monthly_cost' => $results['amount'],
            'tariff_structure_id' => $results['metadata']['tariff_structure_id'] ?? null,
            'power_factor_applied' => 1.0,
            'seasonal_multiplier' => 1.0,
            'appliances_snapshot' => [],
            'calculation_metadata' => $results['metadata'],
        ]);
    }

    /**
     * Generate a unique reference code.
     */
    private function generateReferenceCode(): string
    {
        do {
            $referenceCode = Str::random(8);
        } while (Estimation::where('reference_code', $referenceCode)->exists());

        return $referenceCode;
    }
}
