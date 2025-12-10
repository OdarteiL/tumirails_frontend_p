<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganisationProviderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisation_id',
        'business_registration',
        'service_areas',
        'certifications',
        'rating',
    ];

    protected $casts = [
        'service_areas' => 'array',
        'certifications' => 'array',
        'rating' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the organisation that owns the provider details.
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
