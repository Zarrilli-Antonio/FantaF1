<?php

namespace App\Jobs;

use App\Models\FantasyScore;
use App\Models\League;
use App\Models\Race;
use App\Services\Scoring\FantasyScoreCalculator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * When a league's auction completes, also calculate fantasy scores for the
 * season's races that were already run before the roster was set — otherwise
 * the standings would start from zero, ignoring those earlier races.
 */
class BackfillLeagueScoresJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $leagueId)
    {
    }

    public function handle(FantasyScoreCalculator $calculator): void
    {
        $league = League::with('members.user')->findOrFail($this->leagueId);

        $races = Race::where('season_id', $league->season_id)
            ->where('status', 'completed')
            ->get();

        foreach ($races as $race) {
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
