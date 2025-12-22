<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HardwareType extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'notes',
    ];

    public function hardware(): HasMany
    {
        return $this->hasMany(Hardware::class);
    }
}
