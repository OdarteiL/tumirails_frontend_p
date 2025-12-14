<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TariffTier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tariff_structure_id',
        'min_kwh',
        'max_kwh',
        'rate_per_kwh',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'min_kwh' => 'decimal:2',
        'max_kwh' => 'decimal:2',
        'rate_per_kwh' => 'decimal:4',
        'order' => 'integer',
    ];

    /**
     * Get the tariff structure that owns the tier.
     */
    public function tariffStructure()
    {
        return $this->belongsTo(TariffStructure::class);
    }

    /**
     * Scope a query to order tiers by their order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Check if this tier applies to the given kWh usage.
     */
    public function appliesTo(float $kwh): bool
    {
        if ($kwh < $this->min_kwh) {
            return false;
        }

        if ($this->max_kwh !== null && $kwh > $this->max_kwh) {
            return false;
        }

        return true;
    }

    /**
     * Calculate the cost for usage within this tier.
     */
    public function calculateCost(float $kwh): float
    {
        if (!$this->appliesTo($kwh)) {
            return 0.0;
        }

        $tierKwh = $kwh - $this->min_kwh;
        
        if ($this->max_kwh !== null) {
            $tierKwh = min($tierKwh, $this->max_kwh - $this->min_kwh);
        }

        return $tierKwh * (float) $this->rate_per_kwh;
    }
}
