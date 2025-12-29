<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationBundleComponent extends Model
{
    use HasFactory;

    protected $table = 'recommendation_bundle_components';

    protected $fillable = [
        'bundle_id',
        'hardware_id',
        'role',
        'quantity',
        'total_cost',
        'rationale',
    ];

    protected $casts = [
        'total_cost' => 'decimal:2',
    ];

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(RecommendationBundle::class, 'bundle_id');
    }

    public function hardware(): BelongsTo
    {
        return $this->belongsTo(Hardware::class);
    }
}
