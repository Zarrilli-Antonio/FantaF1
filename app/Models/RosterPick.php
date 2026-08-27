<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RosterPick extends Model
{
    protected $fillable = [
        'league_id', 'user_id', 'auction_round_id', 'pickable_type', 'pickable_id', 'price_paid',
    ];

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auctionRound(): BelongsTo
    {
        return $this->belongsTo(AuctionRound::class);
    }

    public function pickable(): Driver|Constructor|null
    {
        return $this->pickable_type === Bid::TYPE_DRIVER
            ? Driver::find($this->pickable_id)
            : Constructor::find($this->pickable_id);
    }
}
