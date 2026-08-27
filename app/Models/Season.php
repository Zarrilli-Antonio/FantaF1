<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    protected $fillable = ['year', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function races(): HasMany
    {
        return $this->hasMany(Race::class);
    }

    public function leagues(): HasMany
    {
        return $this->hasMany(League::class);
    }
}
