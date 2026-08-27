<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueMember extends Model
{
    protected $fillable = ['league_id', 'user_id', 'budget_remaining', 'joined_at'];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rosterSlotsFilled(string $pickableType): int
    {
        return RosterPick::where('league_id', $this->league_id)
            ->where('user_id', $this->user_id)
            ->where('pickable_type', $pickableType)
            ->count();
    }
}
