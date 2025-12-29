<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Hardware extends Model
{
    use HasFactory;

    protected $fillable = [
        'hardware_type_id',
        'name',
        'description',
        'price',
        'currency',
        'specs',
        'stock_quantity',
        'status',
        'verified',
    ];

    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'price' => 'decimal:2',
            'verified' => 'boolean',
        ];
    }

    public function hardwareType(): BelongsTo
    {
        return $this->belongsTo(HardwareType::class);
    }

    // Legacy provider relation removed. Use the polymorphic `owner` relationship instead.

    public function owner(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'owner_type', 'owner_id');
    }

    public function recommendedHardware(): HasMany
    {
        return $this->hasMany(RecommendedHardware::class);
    }

    // Query Scopes
    public function scopeByType(Builder $query, int $typeId): Builder
    {
        return $query->where('hardware_type_id', $typeId);
    }

    public function scopeByProvider(Builder $query, int $providerId): Builder
    {
        throw new \BadMethodCallException('scopeByProvider has been removed; use owner-based queries instead.');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'active')->where('stock_quantity', '>', 0);
    }

    public function scopeInPriceRange(Builder $query, float $min, float $max): Builder
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verified', true);
    }

    // Accessors
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2);
    }
}
