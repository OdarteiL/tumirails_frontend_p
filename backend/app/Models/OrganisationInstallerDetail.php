<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganisationInstallerDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisation_id',
        'license_number',
        'service_areas',
        'certifications',
        'years_experience',
        'rating',
    ];

    protected $casts = [
        'service_areas' => 'array',
        'certifications' => 'array',
        'years_experience' => 'integer',
        'rating' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the organisation that owns the installer details.
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
