<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganisationInvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organisation_id' => $this->organisation_id,
            'organisation' => $this->when($this->relationLoaded('organisation'), new OrganisationResource($this->organisation)),
            'email' => $this->email,
            'role' => $this->role,
            'invited_by' => $this->invited_by,
            'inviter' => $this->when($this->relationLoaded('inviter'), new UserResource($this->inviter)),
            'expires_at' => $this->expires_at?->toISOString(),
            'accepted_at' => $this->accepted_at?->toISOString(),
            'is_expired' => $this->isExpired(),
            'is_accepted' => $this->isAccepted(),
            'is_valid' => $this->isValid(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
