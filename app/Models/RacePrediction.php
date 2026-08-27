<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RacePrediction extends Model
{
    public const TYPE_POLE = 'pole';
    public const TYPE_FASTEST_PIT_STOP = 'fastest_pit_stop';
    public const TYPE_DNF = 'dnf';

    protected $fillable = ['league_id', 'user_id', 'race_id', 'type', 'pickable_type', 'pickable_id'];

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function pickable(): Driver|Constructor|null
    {
        return $this->pickable_type === Bid::TYPE_DRIVER
            ? Driver::find($this->pickable_id)
            : Constructor::find($this->pickable_id);
    }
}
