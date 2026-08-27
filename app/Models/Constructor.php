<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Constructor extends Model
{
    protected $fillable = ['api_ref', 'name', 'nationality', 'logo_url'];

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    public function raceResults(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }
}
