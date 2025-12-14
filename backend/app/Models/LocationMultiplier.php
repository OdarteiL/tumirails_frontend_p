<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationMultiplier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'country_id',
        'region',
        'city',
        'multiplier',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'multiplier' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the country that owns the location multiplier.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Find multiplier by region and optional city.
     */
    public static function findMultiplier(int $countryId, string $region, ?string $city = null): ?float
    {
        $query = static::where('country_id', $countryId)
            ->where('region', $region)
            ->where('is_active', true);

        if ($city) {
            // Try to find city-specific multiplier first
            $multiplier = $query->clone()->where('city', $city)->first();
            if ($multiplier) {
                return (float) $multiplier->multiplier;
            }
        }

        // Fall back to region-only multiplier
        $multiplier = $query->whereNull('city')->first();
        
        return $multiplier ? (float) $multiplier->multiplier : null;
    }

    /**
     * Scope a query to only include active multipliers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query for a specific region.
     */
    public function scopeForRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Scope a query for a specific city.
     */
    public function scopeForCity($query, string $city)
    {
        return $query->where('city', $city);
    }
}
