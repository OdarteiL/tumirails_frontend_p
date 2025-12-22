<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendedHardware extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimation_id',
        'hardware_id',
        'quantity',
        'total_cost',
        'currency',
        'recommendation_rank',
        'rationale',
    ];

    protected function casts(): array
    {
        return [
            'total_cost' => 'decimal:2',
        ];
    }

    public function estimation(): BelongsTo
    {
        return $this->belongsTo(Estimation::class);
    }

    public function hardware(): BelongsTo
    {
        return $this->belongsTo(Hardware::class);
    }
}
