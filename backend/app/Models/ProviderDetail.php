<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'business_registration',
        'service_areas',
        'certifications',
        'rating',
        'verified',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'service_areas' => 'array',
            'certifications' => 'array',
            'rating' => 'decimal:2',
            'verified' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
