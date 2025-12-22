<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationResource extends JsonResource
{
    protected $recommendations;

    public function __construct($estimation, $recommendations)
    {
        parent::__construct($estimation);
        $this->recommendations = $recommendations;
    }

    public function toArray(Request $request): array
    {
        return [
            'estimation_summary' => [
                'total_watts' => $this->total_watts,
                'daily_kwh' => round($this->monthly_kwh / 30, 1),
                'monthly_kwh' => $this->monthly_kwh,
                'estimated_monthly_cost' => number_format($this->estimated_monthly_cost, 2),
            ],
            'recommendations' => array_map(function ($recommendation, $index) {
                return array_merge($recommendation, [
                    'rank' => $index + 1,
                    'total_price' => number_format($recommendation['total_price'], 2),
                ]);
            }, $this->recommendations, array_keys($this->recommendations)),
        ];
    }
}
