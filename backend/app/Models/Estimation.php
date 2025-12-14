<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estimation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'owner_type',
        'site_id',
        'version',
        'previous_estimation_id',
        'total_watts',
        'daily_kwh',
        'monthly_kwh',
        'estimated_monthly_cost',
        'tariff_structure_id',
        'power_factor_applied',
        'seasonal_multiplier',
        'appliances_snapshot',
        'calculation_metadata',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'total_watts' => 'decimal:2',
            'daily_kwh' => 'decimal:2',
            'monthly_kwh' => 'decimal:2',
            'estimated_monthly_cost' => 'decimal:2',
            'power_factor_applied' => 'decimal:2',
            'seasonal_multiplier' => 'decimal:2',
            'appliances_snapshot' => 'array',
            'calculation_metadata' => 'array',
        ];
    }

    /**
     * Get the owner (User or Organisation) of the estimation.
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the site this estimation belongs to.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the tariff structure used for this estimation.
     */
    public function tariffStructure(): BelongsTo
    {
        return $this->belongsTo(TariffStructure::class);
    }

    /**
     * Get the user who created this estimation.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the previous version of this estimation.
     */
    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(Estimation::class, 'previous_estimation_id');
    }

    /**
     * Get the next version of this estimation.
     */
    public function nextVersion(): HasOne
    {
        return $this->hasOne(Estimation::class, 'previous_estimation_id');
    }
}
