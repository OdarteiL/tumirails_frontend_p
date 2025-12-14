<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TariffStructure extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'country_id',
        'name',
        'type',
        'is_active',
        'effective_date',
        'end_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the country that owns the tariff structure.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the tariff tiers for the tariff structure.
     */
    public function tariffTiers()
    {
        return $this->hasMany(TariffTier::class)->orderBy('order');
    }

    /**
     * Scope a query to only include active tariffs.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)
            ->where('effective_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Check if the tariff structure is currently active.
     */
    public function isActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->effective_date->isAfter($now)) {
            return false;
        }

        if ($this->end_date && $this->end_date->isBefore($now)) {
            return false;
        }

        return true;
    }
}
