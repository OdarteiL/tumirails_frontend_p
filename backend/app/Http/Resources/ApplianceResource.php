<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplianceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'default_wattage' => number_format($this->default_wattage, 2, '.', ''),
            'default_usage_hours' => $this->when(
                $this->default_usage_hours !== null,
                number_format($this->default_usage_hours, 2, '.', '')
            ),
            'metadata' => $this->when(
                $this->metadata !== null,
                $this->metadata
            ),
            'is_public' => $this->is_public,
            'category' => $this->when(
                $this->relationLoaded('category'),
                function () {
                    return [
                        'id' => $this->category->id,
                        'name' => $this->category->name,
                    ];
                }
            ),
            'owner' => $this->when(
                $this->owner_id !== null,
                function () {
                    if ($this->relationLoaded('owner')) {
                        return [
                            'id' => $this->owner->id,
                            'name' => $this->owner->first_name . ' ' . $this->owner->last_name,
                            'type' => class_basename($this->owner_type),
                        ];
                    }
                    return [
                        'id' => $this->owner_id,
                        'type' => class_basename($this->owner_type),
                    ];
                }
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
