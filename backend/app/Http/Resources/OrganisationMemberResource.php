<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganisationMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organisation_id' => $this->organisation_id,
            'user_id' => $this->user_id,
            'user' => $this->when($this->relationLoaded('user'), new UserResource($this->user)),
            'role' => $this->role,
            'joined_at' => $this->joined_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
