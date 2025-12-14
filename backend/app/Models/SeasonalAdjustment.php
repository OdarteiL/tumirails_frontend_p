<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeasonalAdjustment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'country_id',
        'season_name',
        'start_month',
        'end_month',
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
        'start_month' => 'integer',
        'end_month' => 'integer',
        'multiplier' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the country that owns the seasonal adjustment.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Check if the current month falls within this season.
     */
    public function isCurrentSeason(?int $month = null): bool
    {
        $month = $month ?? now()->month;

        if ($this->start_month <= $this->end_month) {
            // Season within the same year (e.g., Apr-Oct)
            return $month >= $this->start_month && $month <= $this->end_month;
        } else {
            // Season crosses year boundary (e.g., Nov-Mar)
            return $month >= $this->start_month || $month <= $this->end_month;
        }
    }

    /**
     * Get the current multiplier if this season is active and current.
     */
    public function getCurrentMultiplier(): ?float
    {
        if (!$this->is_active || !$this->isCurrentSeason()) {
            return null;
        }

        return (float) $this->multiplier;
    }

    /**
     * Scope a query to only include active adjustments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to get current season adjustments.
     */
    public function scopeCurrent($query, ?int $month = null)
    {
        $month = $month ?? now()->month;

        return $query->where(function ($q) use ($month) {
            $q->where(function ($sq) use ($month) {
                // Same year season
                $sq->where('start_month', '<=', 'end_month')
                   ->where('start_month', '<=', $month)
                   ->where('end_month', '>=', $month);
            })->orWhere(function ($sq) use ($month) {
                // Cross-year season
                $sq->where('start_month', '>', 'end_month')
                   ->where(function ($ssq) use ($month) {
                       $ssq->where('start_month', '<=', $month)
                          ->orWhere('end_month', '>=', $month);
                   });
            });
        });
    }
}
