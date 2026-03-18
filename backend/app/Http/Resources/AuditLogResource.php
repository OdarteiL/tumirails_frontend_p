<?php

namespace App\Http\Resources;

use App\Models\Organisation;
use App\Models\OrganisationMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
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
            'action' => $this->action,
            'reason' => $this->reason,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'changes' => $this->getChanges(),
            'performed_by' => $this->when($this->relationLoaded('user') && $this->user, [
                'id' => $this->user?->id,
                'name' => trim("{$this->user?->first_name} {$this->user?->last_name}"),
                'email' => $this->user?->email,
                'role' => $this->user?->role,
            ]),
            'subject' => $this->resolveSubject(),
            'ip_address' => $request->user()?->role === 'admin' ? $this->ip_address : null,
            'user_agent' => $request->user()?->role === 'admin' ? $this->user_agent : null,
            'created_at' => $this->created_at,
        ];
    }

    /**
     * Resolve a human-readable representation of the auditable subject.
     */
    protected function resolveSubject(): array
    {
        if (! $this->relationLoaded('auditable') || ! $this->auditable) {
            return [
                'type' => $this->auditable_type,
                'id' => $this->auditable_id,
            ];
        }

        $auditable = $this->auditable;

        if ($auditable instanceof User) {
            return [
                'type' => 'user',
                'id' => $auditable->id,
                'name' => trim("{$auditable->first_name} {$auditable->last_name}"),
                'email' => $auditable->email,
            ];
        }

        if ($auditable instanceof Organisation) {
            return [
                'type' => 'organisation',
                'id' => $auditable->id,
                'name' => $auditable->name,
                'org_type' => $auditable->type,
            ];
        }

        if ($auditable instanceof OrganisationMember) {
            return [
                'type' => 'organisation_member',
                'id' => $auditable->id,
                'user_id' => $auditable->user_id,
                'organisation_id' => $auditable->organisation_id,
                'role' => $auditable->role,
            ];
        }

        // Generic fallback
        return [
            'type' => class_basename($auditable),
            'id' => $auditable->getKey(),
        ];
    }
}
