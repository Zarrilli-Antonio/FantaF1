<?php

namespace App\Jobs;

use App\Models\FantasyScore;
use App\Models\League;
use App\Models\Race;
use App\Services\Scoring\FantasyScoreCalculator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CalculateFantasyScoresJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $raceId)
    {
    }

    public function handle(FantasyScoreCalculator $calculator): void
    {
        $race = Race::findOrFail($this->raceId);

        $leagues = League::where('season_id', $race->season_id)
            ->where('auction_status', 'completed')
            ->with('members.user')
            ->get();

        foreach ($leagues as $league) {
            foreach ($league->members as $member) {
                $result = $calculator->calculate($league, $member->user, $race);

                FantasyScore::updateOrCreate(
                    ['league_id' => $league->id, 'user_id' => $member->user_id, 'race_id' => $race->id],
                    ['points' => $result['points'], 'breakdown' => $result['breakdown']],
                );
            }
        }
    }
}
