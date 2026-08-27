<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuctionRound extends Model
{
    protected $fillable = ['league_id', 'round_number', 'opens_at', 'closes_at', 'status'];

    protected $casts = [
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
    ];

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function rosterPicks(): HasMany
    {
        return $this->hasMany(RosterPick::class);
    }

    public function isClosed(): bool
    {
        return $this->status !== 'open' || $this->closes_at->isPast();
    }
}
