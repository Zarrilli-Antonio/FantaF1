<?php

namespace App\Console\Commands;

use App\Jobs\ResolveAuctionRoundJob;
use App\Models\AuctionRound;
use Illuminate\Console\Command;

class ResolveDueAuctionRounds extends Command
{
    protected $signature = 'leagues:resolve-due-auction-rounds';

    protected $description = 'Risolve tutti i round d\'asta la cui scadenza è passata';

    public function handle(): int
    {
        $dueRounds = AuctionRound::where('status', 'open')
            ->where('closes_at', '<=', now())
            ->get();

        foreach ($dueRounds as $round) {
            ResolveAuctionRoundJob::dispatch($round->id);
        }

        return self::SUCCESS;
    }
}
