<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Organisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'email',
        'phone',
        'address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the installer details for the organisation.
     */
    public function installerDetail(): HasOne
    {
        return $this->hasOne(OrganisationInstallerDetail::class);
    }

    /**
     * Get the provider details for the organisation.
     */
    public function providerDetail(): HasOne
    {
        return $this->hasOne(OrganisationProviderDetail::class);
    }

    /**
     * Get the members of the organisation.
     */
    public function members(): HasMany
    {
        return $this->hasMany(OrganisationMember::class);
    }

    /**
     * Get the pending invitations for the organisation.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(OrganisationInvitation::class);
    }

    /**
     * Get the sites owned by the organisation.
     */
    public function sites(): MorphMany
    {
        return $this->morphMany(Site::class, 'owner');
    }

    /**
     * Get appliances owned by the organisation.
     */
    public function appliances(): MorphMany
    {
        return $this->morphMany(Appliance::class, 'owner');
    }

    /**
     * Get the owner of the organisation.
     */
    public function owner()
    {
        return $this->members()->where('role', 'owner')->first()?->user;
    }

    /**
     * Check if organisation is of installer type.
     */
    public function isInstaller(): bool
    {
        return $this->type === 'installer';
    }

    /**
     * Check if organisation is of provider type.
     */
    public function isProvider(): bool
    {
        return $this->type === 'provider';
    }

    /**
     * Check if organisation is of customer type.
     */
    public function isCustomer(): bool
    {
        return $this->type === 'customer';
    }
}
