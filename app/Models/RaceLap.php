<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceLap extends Model
{
    protected $fillable = ['race_id', 'driver_id', 'lap', 'position', 'time'];

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Lap time in seconds, parsed from the API's "m:ss.sss" (or plain
     * "ss.sss") string — for plotting, sorting, and computing deltas.
     */
    public function getSecondsAttribute(): ?float
    {
        if (! $this->time) {
            return null;
        }

        if (str_contains($this->time, ':')) {
            [$minutes, $seconds] = explode(':', $this->time, 2);

            return ((float) $minutes) * 60 + (float) $seconds;
        }

        return (float) $this->time;
    }
}
