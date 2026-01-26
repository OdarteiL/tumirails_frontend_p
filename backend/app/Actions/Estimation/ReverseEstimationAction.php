<?php

namespace App\Actions\Estimation;

use Carbon\Carbon;

class ReverseEstimationAction
{
    public function __construct(
        protected CalculateEnergyFromCostAction $calculateEnergyFromCostAction
    ) {}

    public function execute(array $data): array
    {
        $amount = (float) $data['amount'];
        $type = $data['type'];

        if ($type === 'postpaid') {
            $date = Carbon::createFromFormat('Y-m', $data['month'])->startOfMonth();
            $period = [
                'type' => 'postpaid',
                'month' => $data['month'],
                'start' => $date->format('Y-m-d'),
                'end' => $date->copy()->endOfMonth()->format('Y-m-d'),
            ];
        } else {
            $date = Carbon::parse($data['start_date']);
            $period = [
                'type' => 'prepaid',
                'start' => $data['start_date'],
                'end' => $data['end_date'],
            ];
        }

        // Delegate calculation
        $result = $this->calculateEnergyFromCostAction->execute($amount, $date);

        // Append period to metadata
        $result['metadata']['period'] = $period;
        $result['amount'] = $amount;

        return $result;
    }
}
