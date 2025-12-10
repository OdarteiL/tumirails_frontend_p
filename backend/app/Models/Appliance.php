<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appliance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'default_wattage',
        'category_id',
    ];

    protected function casts(): array
    {
        return [
            'default_wattage' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function userAppliances(): HasMany
    {
        return $this->hasMany(UserAppliance::class);
    }
}
