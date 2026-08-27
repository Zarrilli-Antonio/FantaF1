<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bid extends Model
{
    public const TYPE_DRIVER = 'driver';
    public const TYPE_CONSTRUCTOR = 'constructor';

    protected $fillable = [
        'auction_round_id', 'league_id', 'user_id', 'pickable_type', 'pickable_id', 'amount',
    ];

    public function auctionRound(): BelongsTo
    {
        return $this->belongsTo(AuctionRound::class);
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pickable(): Driver|Constructor|null
    {
        return $this->pickable_type === self::TYPE_DRIVER
            ? Driver::find($this->pickable_id)
            : Constructor::find($this->pickable_id);
    }
}
