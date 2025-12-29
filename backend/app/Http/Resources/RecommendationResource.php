<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Actions\Currency\FormatCurrencyAction;

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
        $fmt = new FormatCurrencyAction();

        return [
            'estimation_summary' => [
                'total_watts' => $this->total_watts,
                'daily_kwh' => round($this->monthly_kwh / 30, 1),
                'monthly_kwh' => $this->monthly_kwh,
                'estimated_monthly_cost' => $fmt->formatMeta(floatval($this->estimated_monthly_cost)),
            ],
            'recommendations' => array_map(function ($recommendation, $index) use ($fmt) {
                $meta = $fmt->formatMeta(floatval($recommendation['total_price'] ?? 0));
                return array_merge($recommendation, [
                    'rank' => $index + 1,
                    'total_price' => $recommendation['total_price'],
                    'total_price_formatted' => $meta['formatted'],
                ]);
            }, $this->recommendations, array_keys($this->recommendations)),
        ];
    }
}
