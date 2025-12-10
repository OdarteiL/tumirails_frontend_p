<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SiteAppliance extends Model
{
    use HasFactory;

    protected $table = 'site_appliances';

    protected $fillable = [
        'added_by_id',
        'added_by_type',
        'site_id',
        'appliance_id',
        'quantity',
        'daily_usage_hours',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'daily_usage_hours' => 'decimal:2',
        ];
    }

    /**
     * Get the entity (User or Organisation member) who added the appliance.
     */
    public function addedBy(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Legacy method for backward compatibility.
     *
     * @deprecated Use addedBy() instead
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function appliance(): BelongsTo
    {
        return $this->belongsTo(Appliance::class);
    }
}
