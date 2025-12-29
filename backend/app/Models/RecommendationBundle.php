<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecommendationBundle extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimation_id',
        'owner_type',
        'owner_id',
        'total_cost',
        'currency',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'total_cost' => 'decimal:2',
    ];

    public function estimation(): BelongsTo
    {
        return $this->belongsTo(Estimation::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(RecommendationBundleComponent::class, 'bundle_id');
    }
}
