<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganisationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'installer_detail' => $this->when(
                $this->type === 'installer' && $this->relationLoaded('installerDetail'),
                new OrganisationInstallerDetailResource($this->installerDetail)
            ),
            'provider_detail' => $this->when(
                $this->type === 'provider' && $this->relationLoaded('providerDetail'),
                new OrganisationProviderDetailResource($this->providerDetail)
            ),
            'members_count' => $this->when($this->relationLoaded('members'), fn () => $this->members->count()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
