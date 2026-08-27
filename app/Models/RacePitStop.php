<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RacePitStop extends Model
{
    protected $fillable = ['race_id', 'driver_id', 'stop', 'lap', 'time_of_day', 'duration'];

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Pit stop duration in seconds, parsed from the API's "m:ss.sss" (or
     * plain "ss.sss") string — used to find the fastest stop of the race.
     */
    public function getSecondsAttribute(): ?float
    {
        if (! $this->duration) {
            return null;
        }

        if (str_contains($this->duration, ':')) {
            [$minutes, $seconds] = explode(':', $this->duration, 2);

            return ((float) $minutes) * 60 + (float) $seconds;
        }

        return (float) $this->duration;
    }
}
