<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'owner_type',
        'name',
        'address',
        'latitude',
        'longitude',
        'timezone',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    /**
     * Get the owner (User or Organisation) of the site.
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Legacy method for backward compatibility.
     * @deprecated Use owner() instead
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
