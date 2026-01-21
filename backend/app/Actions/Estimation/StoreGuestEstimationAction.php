<?php

namespace App\Actions\Estimation;

use App\Models\Estimation;
use Carbon\Carbon;
use Illuminate\Support\Str;

class StoreGuestEstimationAction
{
    protected CalculateGuestEstimationAction $calculateGuestEstimationAction;

    public function __construct(
        ?CalculateGuestEstimationAction $calculateGuestEstimationAction = null
    ) {
        $this->calculateGuestEstimationAction = $calculateGuestEstimationAction ?? app(CalculateGuestEstimationAction::class);
    }

    public function execute(array $data): Estimation
    {
        $estimationData = $this->calculateGuestEstimationAction->execute($data['appliances']);

        $estimation = Estimation::create([
            'reference_code' => $this->generateReferenceCode(),
            'expires_at' => Carbon::now()->addDays(config('features.guest_estimation_ttl_days', 30)),
            'total_watts' => $estimationData['total_watts'],
            'daily_kwh' => $estimationData['daily_kwh'],
            'monthly_kwh' => $estimationData['monthly_kwh'],
            'estimated_monthly_cost' => $estimationData['estimated_monthly_cost'],
            'appliances_snapshot' => $estimationData['appliances_breakdown'],
            'calculation_metadata' => $estimationData['calculation_metadata'],
        ]);

        return $estimation;
    }

    private function generateReferenceCode(): string
    {
        do {
            $referenceCode = Str::random(8);
        } while (Estimation::where('reference_code', $referenceCode)->exists());

        return $referenceCode;
    }
}
