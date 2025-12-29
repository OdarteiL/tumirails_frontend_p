<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // The Site model is polymorphic (owner_id + owner_type).
        // For backward compatibility we expose `user_id` when the owner is a User,
        // and also include an explicit `owner` object so callers can handle organisation owners too.
        $userId = null;

        if ($this->owner_type === \App\Models\User::class) {
            $userId = $this->owner_id;
        }

        return [
            'id' => $this->id,
            'user_id' => $userId,
            'owner' => [
                'id' => $this->owner_id,
                'type' => $this->owner_type ? class_basename($this->owner_type) : null,
            ],
            'name' => $this->name,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'timezone' => $this->timezone,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
