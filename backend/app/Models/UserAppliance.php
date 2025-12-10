<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAppliance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
