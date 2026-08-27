<?php

namespace App\Jobs;

use App\Models\AuctionRound;
use App\Services\Auction\AuctionResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ResolveAuctionRoundJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $auctionRoundId)
    {
    }

    public function handle(AuctionResolver $resolver): void
    {
        $round = AuctionRound::findOrFail($this->auctionRoundId);

        if ($round->status === 'open') {
            $resolver->resolve($round);
        }
    }
}
