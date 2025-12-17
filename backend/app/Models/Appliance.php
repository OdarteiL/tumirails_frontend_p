<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Appliance extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'owner_type',
        'name',
        'default_wattage',
        'category_id',
        'default_usage_hours',
        'metadata',
        'is_public',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_wattage' => 'decimal:2',
            'default_usage_hours' => 'decimal:2',
            'metadata' => 'array',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot the model and add global scope to filter inactive appliances.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $query) {
            $query->where('is_active', true);
        });
    }

    /**
     * Get the owner (User or Organisation) of the appliance.
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Legacy method for backward compatibility.
     *
     * @deprecated Use owner() instead
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function siteAppliances(): HasMany
    {
        return $this->hasMany(SiteAppliance::class);
    }

    /**
     * Scope to filter public appliances.
     */
    public function scopePublic(Builder $query): void
    {
        $query->where('is_public', true);
    }

    /**
     * Scope to filter active appliances (includes inactive when using withInactive).
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope to filter appliances owned by a specific owner.
     */
    public function scopeOwnedBy(Builder $query, int $ownerId, string $ownerType = User::class): void
    {
        $query->where('owner_id', $ownerId)
              ->where('owner_type', $ownerType);
    }

    /**
     * Scope to filter appliances visible to a user (public or owned by user/organisation).
     */
    public function scopeVisibleTo(Builder $query, int $ownerId, string $ownerType = User::class): void
    {
        $query->where(function ($q) use ($ownerId, $ownerType) {
            $q->where('is_public', true)
              ->orWhere(function ($subQ) use ($ownerId, $ownerType) {
                  $subQ->where('owner_id', $ownerId)
                       ->where('owner_type', $ownerType);
              });
        });
    }

    /**
     * Scope to include inactive appliances.
     */
    public function scopeWithInactive(Builder $query): void
    {
        $query->withoutGlobalScope('active');
    }

    /**
     * Get the efficiency rating from metadata.
     */
    public function getEfficiencyRatingAttribute(): ?string
    {
        return $this->metadata['efficiency_rating'] ?? null;
    }
}
