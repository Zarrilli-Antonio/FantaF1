<?php

namespace App\Services\Scoring;

use App\Models\Bid;
use App\Models\League;
use App\Models\Race;
use App\Models\RacePitStop;
use App\Models\RacePrediction;
use App\Models\RaceResult;
use App\Models\RosterPick;
use App\Models\User;
use Illuminate\Support\Collection;

class FantasyScoreCalculator
{
    /**
     * @return array{points: float, breakdown: array<string, mixed>}
     */
    public function calculate(League $league, User $user, Race $race): array
    {
        $picks = RosterPick::where('league_id', $league->id)
            ->where('user_id', $user->id)
            ->get();

        $results = RaceResult::where('race_id', $race->id)->get();

        $driverBreakdown = [];
        foreach ($picks->where('pickable_type', Bid::TYPE_DRIVER) as $pick) {
            $result = $results->firstWhere('driver_id', $pick->pickable_id);
            $driverBreakdown[] = $this->scoreDriver($pick->pickable_id, $result);
        }

        $constructorBreakdown = [];
        foreach ($picks->where('pickable_type', Bid::TYPE_CONSTRUCTOR) as $pick) {
            $constructorBreakdown[] = $this->scoreConstructor($pick->pickable_id, $results);
        }

        $predictionBreakdown = $this->scorePredictions($league, $user, $race, $results);

        $total = array_sum(array_column($driverBreakdown, 'points'))
            + array_sum(array_column($constructorBreakdown, 'points'))
            + array_sum(array_column($predictionBreakdown, 'points'));

        return [
            'points' => $total,
            'breakdown' => [
                'drivers' => $driverBreakdown,
                'constructors' => $constructorBreakdown,
                'predictions' => $predictionBreakdown,
            ],
        ];
    }

    /**
     * @return array{driver_id: int, points: float, detail: array<string, mixed>}
     */
    private function scoreDriver(int $driverId, ?RaceResult $result): array
    {
        if (! $result) {
            return ['driver_id' => $driverId, 'points' => 0.0, 'detail' => ['reason' => 'no_result']];
        }

        $positionPoints = config("fantasy.driver_position_points.{$result->position}", 0);
        $points = (float) $positionPoints;
        $detail = ['position_points' => $positionPoints];

        if ($result->grid === 1) {
            $points += config('fantasy.bonus.pole_position', 0);
            $detail['pole_bonus'] = config('fantasy.bonus.pole_position', 0);
        }

        if ($result->fastest_lap) {
            $points += config('fantasy.bonus.fastest_lap', 0);
            $detail['fastest_lap_bonus'] = config('fantasy.bonus.fastest_lap', 0);
        }

        if ($result->position !== null && $result->position <= 3) {
            $points += config('fantasy.bonus.podium', 0);
            $detail['podium_bonus'] = config('fantasy.bonus.podium', 0);
        }

        if ($result->isDnf()) {
            $points += config('fantasy.penalty.dnf', 0);
            $detail['dnf_penalty'] = config('fantasy.penalty.dnf', 0);
        }

        return ['driver_id' => $driverId, 'points' => $points, 'detail' => $detail];
    }

    /**
     * @param Collection<int, RaceResult> $results
     * @return array{constructor_id: int, points: float, detail: array<string, mixed>}
     */
    private function scoreConstructor(int $constructorId, Collection $results): array
    {
        $teamResults = $results->where('constructor_id', $constructorId);
        $realPoints = (float) $teamResults->sum('real_points');
        $multiplier = (float) config('fantasy.constructor_points_multiplier', 1.0);

        return [
            'constructor_id' => $constructorId,
            'points' => $realPoints * $multiplier,
            'detail' => ['real_points' => $realPoints, 'multiplier' => $multiplier],
        ];
    }

    /**
     * Race-by-race predictions: who takes pole, who retires, which team
     * makes the fastest pit stop. Each one earns the points configured for
     * the league (or the global default) only if the user got the
     * prediction right — no penalty otherwise. Predictions can be made on
     * any driver/team, not just the ones on the user's roster.
     *
     * @param Collection<int, RaceResult> $results
     * @return array<int, array{type: string, pickable_type: string, pickable_id: int, correct: bool, points: float}>
     */
    private function scorePredictions(League $league, User $user, Race $race, Collection $results): array
    {
        $predictions = RacePrediction::where('league_id', $league->id)
            ->where('user_id', $user->id)
            ->where('race_id', $race->id)
            ->get();

        if ($predictions->isEmpty()) {
            return [];
        }

        $outcomes = $this->raceOutcomes($race, $results);

        return $predictions->map(function (RacePrediction $prediction) use ($league, $outcomes) {
            $correct = match ($prediction->type) {
                RacePrediction::TYPE_POLE => $prediction->pickable_id === $outcomes['pole_driver_id'],
                RacePrediction::TYPE_DNF => in_array($prediction->pickable_id, $outcomes['dnf_driver_ids'], true),
                RacePrediction::TYPE_FASTEST_PIT_STOP => $prediction->pickable_id === $outcomes['fastest_pit_stop_constructor_id'],
                default => false,
            };

            $points = $correct ? $league->predictionPoints($prediction->type) : 0;

            return [
                'type' => $prediction->type,
                'pickable_type' => $prediction->pickable_type,
                'pickable_id' => $prediction->pickable_id,
                'correct' => $correct,
                'points' => (float) $points,
            ];
        })->values()->all();
    }

    /**
     * @param Collection<int, RaceResult> $results
     * @return array{pole_driver_id: ?int, dnf_driver_ids: array<int, int>, fastest_pit_stop_constructor_id: ?int}
     */
    private function raceOutcomes(Race $race, Collection $results): array
    {
        $poleDriverId = $results->firstWhere('grid', 1)?->driver_id;
        $dnfDriverIds = $results->filter(fn (RaceResult $r) => $r->isDnf())->pluck('driver_id')->all();

        $fastestPitStop = RacePitStop::where('race_id', $race->id)
            ->get()
            ->sortBy(fn (RacePitStop $stop) => $stop->seconds ?? INF)
            ->first();

        $fastestPitStopConstructorId = $fastestPitStop
            ? $results->firstWhere('driver_id', $fastestPitStop->driver_id)?->constructor_id
            : null;

        return [
            'pole_driver_id' => $poleDriverId,
            'dnf_driver_ids' => $dnfDriverIds,
            'fastest_pit_stop_constructor_id' => $fastestPitStopConstructorId,
        ];
    }
}
