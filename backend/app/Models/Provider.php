<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'business_registration',
        'description',
        'rating',
        'verified',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'verified' => 'boolean',
        ];
    }

    public function hardware(): HasMany
    {
        return $this->hasMany(Hardware::class);
    }
}
