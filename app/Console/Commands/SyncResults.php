<?php

namespace App\Console\Commands;

use App\Jobs\CalculateFantasyScoresJob;
use App\Models\Constructor;
use App\Models\Driver;
use App\Models\Race;
use App\Models\RaceLap;
use App\Models\RacePitStop;
use App\Models\RaceResult;
use App\Services\F1Api\JolpicaClient;
use Illuminate\Console\Command;

class SyncResults extends Command
{
    protected $signature = 'f1:sync-results {race : ID della gara (tabella races)}';

    protected $description = 'Importa i risultati di una gara F1 dalla Jolpica API e ricalcola i punteggi fantasy';

    public function handle(JolpicaClient $client): int
    {
        $race = Race::with('season')->findOrFail((int) $this->argument('race'));
        $year = $race->season->year;

        $results = $client->raceResults($year, $race->round);

        if (empty($results)) {
            $this->components->warn("Nessun risultato disponibile ancora per {$race->name}.");

            return self::SUCCESS;
        }

        foreach ($results as $row) {
            $driver = Driver::where('api_ref', $row['Driver']['driverId'])->first();
            $constructor = Constructor::where('api_ref', $row['Constructor']['constructorId'])->first();

            if (! $driver || ! $constructor) {
                $this->components->warn("Pilota o costruttore non trovato per la riga: {$row['Driver']['driverId']}");

                continue;
            }

            // The API assigns `position` a sequential rank even to drivers who
            // retired; the reliable signal for official classification is
            // `positionText`: numeric if the driver was classified (even if
            // lapped or retired near the end of the race), "R"/"D"/"W"/"E"/"N"
            // if not.
            $status = $row['status'] ?? 'Unknown';
            $finished = ctype_digit((string) ($row['positionText'] ?? ''));

            RaceResult::updateOrCreate(
                ['race_id' => $race->id, 'driver_id' => $driver->id],
                [
                    'constructor_id' => $constructor->id,
                    'grid' => $row['grid'] ?? null,
                    'laps' => $row['laps'] ?? null,
                    'race_time' => $row['Time']['time'] ?? null,
                    'position' => $finished ? $row['position'] : null,
                    'real_points' => $row['points'] ?? 0,
                    'fastest_lap' => ($row['FastestLap']['rank'] ?? null) === '1',
                    'fastest_lap_time' => $row['FastestLap']['Time']['time'] ?? null,
                    'fastest_lap_number' => $row['FastestLap']['lap'] ?? null,
                    'status' => $finished ? 'Finished' : $status,
                ],
            );
        }

        $race->update(['status' => 'completed']);

        $this->syncLapsAndPitStops($client, $race, $year);

        $this->components->info("Risultati di {$race->name} importati.");

        CalculateFantasyScoresJob::dispatch($race->id);

        return self::SUCCESS;
    }

    private function syncLapsAndPitStops(JolpicaClient $client, Race $race, int $year): void
    {
        $driverIdsByApiRef = Driver::pluck('id', 'api_ref');

        $laps = collect($client->raceLaps($year, $race->round))
            ->map(function ($lap) use ($race, $driverIdsByApiRef) {
                $driverId = $driverIdsByApiRef->get($lap['driverId']);

                return $driverId ? [
                    'race_id' => $race->id,
                    'driver_id' => $driverId,
                    'lap' => $lap['lap'],
                    'position' => $lap['position'],
                    'time' => $lap['time'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ] : null;
            })
            ->filter()
            ->values()
            ->all();

        foreach (array_chunk($laps, 500) as $chunk) {
            RaceLap::upsert($chunk, ['race_id', 'driver_id', 'lap'], ['position', 'time', 'updated_at']);
        }

        $pitStops = collect($client->racePitStops($year, $race->round))
            ->map(function ($stop) use ($race, $driverIdsByApiRef) {
                $driverId = $driverIdsByApiRef->get($stop['driverId']);

                return $driverId ? [
                    'race_id' => $race->id,
                    'driver_id' => $driverId,
                    'stop' => $stop['stop'],
                    'lap' => $stop['lap'],
                    'time_of_day' => $stop['time'] ?? null,
                    'duration' => $stop['duration'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ] : null;
            })
            ->filter()
            ->values()
            ->all();

        if ($pitStops) {
            RacePitStop::upsert($pitStops, ['race_id', 'driver_id', 'stop'], ['lap', 'time_of_day', 'duration', 'updated_at']);
        }
    }
}
