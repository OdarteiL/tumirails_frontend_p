<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationBundleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'estimation_id' => $this->estimation_id,
            'owner_type' => $this->owner_type,
            'owner_id' => $this->owner_id,
            'total_cost' => (float) $this->total_cost,
            'currency' => $this->currency,
            'metadata' => $this->metadata,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toDateTimeString(),
            'components' => RecommendationBundleComponentResource::collection($this->whenLoaded('components')),
        ];
    }
}
