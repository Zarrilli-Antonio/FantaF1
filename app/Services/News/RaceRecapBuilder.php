<?php

namespace App\Services\News;

use App\Models\Race;
use App\Models\RacePitStop;
use Illuminate\Support\Collection;

class RaceRecapBuilder
{
    /**
     * Builds a text recap of a finished race from the imported official
     * results (winner, podium, pole, fastest lap, retirements, fastest pit
     * stop).
     *
     * @return array{winner: ?string, podium: array<int, string>, pole: ?string, fastestLap: ?string, fastestPitStop: ?array{driver: string, constructor: ?string, duration: string}, retirements: array<int, string>, summary: string}
     */
    public function build(Race $race): array
    {
        $results = $race->results()->with(['driver', 'constructor'])->orderBy('position')->get();

        $podiumResults = $results->whereNotNull('position')->where('position', '<=', 3)->sortBy('position');
        $winner = $podiumResults->firstWhere('position', 1);
        $poleResult = $results->firstWhere('grid', 1);
        $fastestLapResult = $results->firstWhere('fastest_lap', true);
        $retirements = $results->filter(fn ($r) => $r->isDnf());

        $fastestPitStop = RacePitStop::where('race_id', $race->id)
            ->get()
            ->sortBy(fn (RacePitStop $stop) => $stop->seconds ?? INF)
            ->first();

        $fastestPitStopResult = $fastestPitStop ? $results->firstWhere('driver_id', $fastestPitStop->driver_id) : null;

        return [
            'winner' => $winner?->driver->full_name,
            'winnerConstructor' => $winner?->constructor->name,
            'podium' => $podiumResults->map(fn ($r) => $r->driver->full_name)->values()->all(),
            'pole' => $poleResult?->driver->full_name,
            'fastestLap' => $fastestLapResult?->driver->full_name,
            'fastestPitStop' => $fastestPitStop ? [
                'driver' => $fastestPitStopResult?->driver->full_name ?? $fastestPitStop->driver->full_name,
                'constructor' => $fastestPitStopResult?->constructor->name,
                'duration' => $fastestPitStop->duration,
            ] : null,
            'retirements' => $retirements->map(fn ($r) => $r->driver->full_name)->values()->all(),
            'summary' => $this->summary($race, $winner, $podiumResults, $poleResult, $fastestLapResult, $fastestPitStop, $fastestPitStopResult, $retirements),
        ];
    }

    private function summary(Race $race, $winner, Collection $podiumResults, $poleResult, $fastestLapResult, ?RacePitStop $fastestPitStop, $fastestPitStopResult, Collection $retirements): string
    {
        if (! $winner) {
            return __('Risultati di :race non ancora disponibili.', ['race' => $race->name]);
        }

        $sentences = [];

        $sentences[] = __('Vittoria per :driver (:team) al :race.', [
            'driver' => $winner->driver->full_name,
            'team' => $winner->constructor->name,
            'race' => $race->name,
        ]);

        $others = $podiumResults->where('position', '!=', 1)->map(fn ($r) => $r->driver->full_name);
        if ($others->isNotEmpty()) {
            $sentences[] = __('Podio completato da :drivers.', ['drivers' => $others->implode(' '.__('e').' ')]);
        }

        if ($poleResult && $fastestLapResult) {
            if ($poleResult->driver_id === $fastestLapResult->driver_id) {
                $sentences[] = __(':driver si aggiudica sia la pole position che il giro più veloce.', ['driver' => $poleResult->driver->full_name]);
            } else {
                $sentences[] = __('Pole position per :pole, giro più veloce per :fastest.', [
                    'pole' => $poleResult->driver->full_name,
                    'fastest' => $fastestLapResult->driver->full_name,
                ]);
            }
        }

        if ($fastestPitStop) {
            $driverName = $fastestPitStopResult?->driver->full_name ?? $fastestPitStop->driver->full_name;
            $constructorName = $fastestPitStopResult?->constructor->name;

            $sentences[] = $constructorName
                ? __('Pit stop più veloce per :driver (:team) in :duration secondi.', [
                    'driver' => $driverName,
                    'team' => $constructorName,
                    'duration' => $fastestPitStop->duration,
                ])
                : __('Pit stop più veloce per :driver in :duration secondi.', [
                    'driver' => $driverName,
                    'duration' => $fastestPitStop->duration,
                ]);
        }

        if ($retirements->isNotEmpty()) {
            $names = $retirements->map(fn ($r) => $r->driver->full_name)->implode(', ');
            $sentences[] = $retirements->count() === 1
                ? __('Ritiro per :names.', ['names' => $names])
                : __('Ritiri per :names.', ['names' => $names]);
        }

        return implode(' ', $sentences);
    }
}
