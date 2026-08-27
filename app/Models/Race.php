<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Race extends Model
{
    protected $fillable = [
        'season_id', 'api_ref', 'round', 'name', 'circuit', 'country', 'locality',
        'latitude', 'longitude', 'wikipedia_url', 'circuit_wikipedia_url', 'starts_at', 'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }

    public function fantasyScores(): HasMany
    {
        return $this->hasMany(FantasyScore::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
