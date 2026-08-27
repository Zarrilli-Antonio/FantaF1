<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceResult extends Model
{
    protected $fillable = [
        'race_id', 'driver_id', 'constructor_id', 'grid', 'position', 'laps', 'race_time',
        'real_points', 'fastest_lap', 'fastest_lap_time', 'fastest_lap_number', 'status',
    ];

    protected $casts = [
        'fastest_lap' => 'boolean',
        'real_points' => 'float',
    ];

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function constructor(): BelongsTo
    {
        return $this->belongsTo(Constructor::class);
    }

    public function isDnf(): bool
    {
        return $this->status !== 'Finished';
    }
}
