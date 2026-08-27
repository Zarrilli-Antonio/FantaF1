<?php

namespace App\Console\Commands;

use App\Models\Constructor;
use App\Models\Driver;
use App\Models\Race;
use App\Models\Season;
use App\Services\F1Api\JolpicaClient;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncSeason extends Command
{
    protected $signature = 'f1:sync-season {year? : Anno del campionato, es. 2026 (default: anno corrente)}';

    protected $description = 'Importa calendario gare, piloti e costruttori di una stagione F1 dalla Jolpica API';

    public function handle(JolpicaClient $client): int
    {
        $year = (int) ($this->argument('year') ?? now()->year);

        $season = Season::updateOrCreate(['year' => $year], ['year' => $year]);

        $this->components->info("Sincronizzazione costruttori {$year}...");
        foreach ($client->seasonConstructors($year) as $row) {
            Constructor::updateOrCreate(
                ['api_ref' => $row['constructorId']],
                [
                    'name' => $row['name'],
                    'nationality' => $row['nationality'] ?? null,
                ],
            );
        }

        $this->components->info("Sincronizzazione piloti {$year}...");
        foreach ($client->seasonDrivers($year) as $row) {
            Driver::updateOrCreate(
                ['api_ref' => $row['driverId']],
                [
                    'first_name' => $row['givenName'],
                    'last_name' => $row['familyName'],
                    'code' => $row['code'] ?? null,
                    'number' => $row['permanentNumber'] ?? null,
                    'nationality' => $row['nationality'] ?? null,
                    'date_of_birth' => $row['dateOfBirth'] ?? null,
                    'wikipedia_url' => $row['url'] ?? null,
                ],
            );
        }

        $this->components->info("Sincronizzazione calendario {$year}...");
        foreach ($client->seasonSchedule($year) as $row) {
            $startsAt = Carbon::parse($row['date'] . ' ' . ($row['time'] ?? '00:00:00'));

            Race::updateOrCreate(
                ['api_ref' => "{$year}-{$row['round']}"],
                [
                    'season_id' => $season->id,
                    'round' => $row['round'],
                    'name' => $row['raceName'],
                    'circuit' => $row['Circuit']['circuitName'] ?? '',
                    'country' => $row['Circuit']['Location']['country'] ?? null,
                    'locality' => $row['Circuit']['Location']['locality'] ?? null,
                    'latitude' => $row['Circuit']['Location']['lat'] ?? null,
                    'longitude' => $row['Circuit']['Location']['long'] ?? null,
                    'wikipedia_url' => $row['url'] ?? null,
                    'circuit_wikipedia_url' => $row['Circuit']['url'] ?? null,
                    'starts_at' => $startsAt,
                ],
            );
        }

        $this->components->info("Stagione {$year} sincronizzata.");

        return self::SUCCESS;
    }
}
