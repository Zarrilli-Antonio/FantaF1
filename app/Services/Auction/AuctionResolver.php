<?php

namespace App\Services\Auction;

use App\Jobs\BackfillLeagueScoresJob;
use App\Models\AuctionRound;
use App\Models\Bid;
use App\Models\LeagueMember;
use App\Models\RosterPick;
use Illuminate\Support\Facades\DB;

class AuctionResolver
{
    /**
     * Resolve an auction round: assign each driver/team to the highest bidder
     * still eligible (open slot, available budget), then open the next round
     * if the league still has slots to fill, otherwise close the league's auction.
     */
    public function resolve(AuctionRound $round): void
    {
        DB::transaction(function () use ($round) {
            $league = $round->league;

            $bids = Bid::where('auction_round_id', $round->id)
                ->orderByDesc('amount')
                ->orderBy('created_at')
                ->get();

            $members = LeagueMember::where('league_id', $league->id)->get()->keyBy('user_id');

            $slotsFilled = [];
            foreach ($members as $userId => $member) {
                $slotsFilled[$userId] = [
                    Bid::TYPE_DRIVER => $member->rosterSlotsFilled(Bid::TYPE_DRIVER),
                    Bid::TYPE_CONSTRUCTOR => $member->rosterSlotsFilled(Bid::TYPE_CONSTRUCTOR),
                ];
            }

            $assignedItems = RosterPick::where('league_id', $league->id)
                ->get()
                ->map(fn ($pick) => "{$pick->pickable_type}:{$pick->pickable_id}")
                ->flip();

            foreach ($bids as $bid) {
                $itemKey = "{$bid->pickable_type}:{$bid->pickable_id}";
                $member = $members[$bid->user_id] ?? null;

                if (! $member || $assignedItems->has($itemKey)) {
                    continue;
                }

                $slotLimit = $bid->pickable_type === Bid::TYPE_DRIVER ? $league->driver_slots : $league->constructor_slots;
                if ($slotsFilled[$bid->user_id][$bid->pickable_type] >= $slotLimit) {
                    continue;
                }

                if ($member->budget_remaining < $bid->amount) {
                    continue;
                }

                RosterPick::create([
                    'league_id' => $league->id,
                    'user_id' => $bid->user_id,
                    'auction_round_id' => $round->id,
                    'pickable_type' => $bid->pickable_type,
                    'pickable_id' => $bid->pickable_id,
                    'price_paid' => $bid->amount,
                ]);

                $member->decrement('budget_remaining', $bid->amount);
                $slotsFilled[$bid->user_id][$bid->pickable_type]++;
                $assignedItems->put($itemKey, true);
            }

            $round->update(['status' => 'resolved']);

            $this->openNextRoundOrComplete($league, $members, $slotsFilled);
        });
    }

    /**
     * @param \Illuminate\Support\Collection<int, LeagueMember> $members
     * @param array<int, array<string, int>> $slotsFilled
     */
    private function openNextRoundOrComplete($league, $members, array $slotsFilled): void
    {
        $everyoneComplete = $members->every(function (LeagueMember $member) use ($league, $slotsFilled) {
            $filled = $slotsFilled[$member->user_id];

            return $filled[Bid::TYPE_DRIVER] >= $league->driver_slots
                && $filled[Bid::TYPE_CONSTRUCTOR] >= $league->constructor_slots;
        });

        if ($everyoneComplete) {
            $league->update(['auction_status' => 'completed']);

            BackfillLeagueScoresJob::dispatch($league->id)->afterCommit();

            return;
        }

        $nextRoundNumber = ($league->auctionRounds()->max('round_number') ?? 0) + 1;
        $durationHours = config('fantasy.auction_round_duration_hours', 48);

        $league->auctionRounds()->create([
            'round_number' => $nextRoundNumber,
            'opens_at' => now(),
            'closes_at' => now()->addHours($durationHours),
            'status' => 'open',
        ]);

        $league->update(['auction_status' => 'round_open']);
    }
}
