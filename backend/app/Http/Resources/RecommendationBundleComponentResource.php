<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationBundleComponentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'hardware_id' => $this->hardware_id,
            'quantity' => $this->quantity,
            'total_cost' => (float) $this->total_cost,
            'role' => $this->role,
            'rationale' => $this->rationale,
            'hardware' => $this->whenLoaded('hardware', function () {
                return [
                    'id' => $this->hardware->id,
                    'name' => $this->hardware->name,
                    'price' => (float) $this->hardware->price,
                    'currency' => $this->hardware->currency,
                    'hardware_type' => $this->hardware->hardwareType?->name,
                ];
            }),
        ];
    }
}
