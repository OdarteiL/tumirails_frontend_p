<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'other_names',
        'email',
        'password',
        'phone',
        'address',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getFullNameAttribute(): string
    {
        $name = $this->first_name;

        if ($this->other_names) {
            $name .= ' '.$this->other_names;
        }

        $name .= ' '.$this->last_name;

        return $name;
    }

    public function sites(): MorphMany
    {
        return $this->morphMany(Site::class, 'owner');
    }

    /**
     * Get appliances owned by the user.
     */
    public function appliances(): MorphMany
    {
        return $this->morphMany(Appliance::class, 'owner');
    }

    /**
     * Get site appliances added by the user.
     */
    public function siteAppliancesAdded(): MorphMany
    {
        return $this->morphMany(SiteAppliance::class, 'added_by');
    }

    /**
     * Get estimations owned by the user.
     */
    public function estimations(): MorphMany
    {
        return $this->morphMany(Estimation::class, 'owner');
    }

    public function installerDetail(): HasOne
    {
        return $this->hasOne(InstallerDetail::class);
    }

    public function providerDetail(): HasOne
    {
        return $this->hasOne(ProviderDetail::class);
    }

    /**
     * Get the organisation memberships for the user.
     */
    public function organisationMemberships(): HasMany
    {
        return $this->hasMany(OrganisationMember::class);
    }

    /**
     * Get all organisations the user belongs to.
     */
    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class, 'organisation_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Check if user belongs to an organisation.
     */
    public function belongsToOrganisation(int $organisationId): bool
    {
        return $this->organisations()->where('organisations.id', $organisationId)->exists();
    }

    /**
     * Get user's role in a specific organisation.
     */
    public function roleInOrganisation(int $organisationId): ?string
    {
        $membership = $this->organisationMemberships()
            ->where('organisation_id', $organisationId)
            ->first();

        return $membership?->role;
    }
}
