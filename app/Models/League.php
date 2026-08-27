<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class League extends Model
{
    protected $fillable = [
        'name', 'owner_id', 'season_id', 'invite_code',
        'driver_slots', 'constructor_slots', 'budget', 'auction_status', 'prediction_points',
    ];

    protected $casts = [
        'prediction_points' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (League $league) {
            $league->invite_code ??= Str::upper(Str::random(8));
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(LeagueMember::class);
    }

    public function auctionRounds(): HasMany
    {
        return $this->hasMany(AuctionRound::class);
    }

    public function rosterPicks(): HasMany
    {
        return $this->hasMany(RosterPick::class);
    }

    public function fantasyScores(): HasMany
    {
        return $this->hasMany(FantasyScore::class);
    }

    public function racePredictions(): HasMany
    {
        return $this->hasMany(RacePrediction::class);
    }

    /**
     * Points for a correct prediction of a given type, using the value
     * chosen for this league at creation if present, otherwise the global default.
     */
    public function predictionPoints(string $type): int
    {
        return (int) ($this->prediction_points[$type] ?? config("fantasy.prediction_points.{$type}", 0));
    }

    public function currentAuctionRound(): ?AuctionRound
    {
        return $this->auctionRounds()->where('status', 'open')->latest('round_number')->first();
    }

    public function totalSlotsPerMember(): int
    {
        return $this->driver_slots + $this->constructor_slots;
    }
}
