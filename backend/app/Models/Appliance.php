<?php

namespace App\Models;

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
    ];

    protected function casts(): array
    {
        return [
            'default_wattage' => 'decimal:2',
        ];
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
}
