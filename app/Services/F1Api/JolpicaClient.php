<?php

namespace App\Services\F1Api;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class JolpicaClient
{
    public function __construct(
        private readonly string $baseUrl = 'https://api.jolpi.ca/ergast/f1',
    ) {
    }

    /**
     * @return array<int, array<string, mixed>> Race calendar entries for a season.
     */
    public function seasonSchedule(int $year): array
    {
        $data = $this->get("/{$year}.json");

        return $data['MRData']['RaceTable']['Races'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>> Driver entries for a season.
     */
    public function seasonDrivers(int $year): array
    {
        $data = $this->get("/{$year}/drivers.json", ['limit' => 100]);

        return $data['MRData']['DriverTable']['Drivers'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>> Constructor entries for a season.
     */
    public function seasonConstructors(int $year): array
    {
        $data = $this->get("/{$year}/constructors.json", ['limit' => 100]);

        return $data['MRData']['ConstructorTable']['Constructors'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>> Result rows for a single race (round).
     */
    public function raceResults(int $year, int $round): array
    {
        $data = $this->get("/{$year}/{$round}/results.json", ['limit' => 100]);
        $races = $data['MRData']['RaceTable']['Races'] ?? [];

        return $races[0]['Results'] ?? [];
    }

    /**
     * @return array<int, array{lap: int, driverId: string, position: ?string, time: ?string}>
     */
    public function raceLaps(int $year, int $round): array
    {
        $all = [];
        $offset = 0;

        do {
            $data = $this->get("/{$year}/{$round}/laps.json", ['limit' => 100, 'offset' => $offset]);
            $laps = $data['MRData']['RaceTable']['Races'][0]['Laps'] ?? [];

            foreach ($laps as $lapEntry) {
                foreach ($lapEntry['Timings'] ?? [] as $timing) {
                    $all[] = [
                        'lap' => (int) $lapEntry['number'],
                        'driverId' => $timing['driverId'],
                        'position' => $timing['position'] ?? null,
                        'time' => $timing['time'] ?? null,
                    ];
                }
            }

            $offset += 100;

            // Paginated series of requests against a free, shared API — a short
            // pause between pages avoids tripping its rate limit on long races.
            if ($offset < (int) ($data['MRData']['total'] ?? 0)) {
                usleep(300_000);
            }
        } while ($offset < (int) ($data['MRData']['total'] ?? 0));

        return $all;
    }

    /**
     * @return array<int, array{driverId: string, stop: string, lap: string, time: ?string, duration: ?string}>
     */
    public function racePitStops(int $year, int $round): array
    {
        $all = [];
        $offset = 0;

        do {
            $data = $this->get("/{$year}/{$round}/pitstops.json", ['limit' => 100, 'offset' => $offset]);
            $stops = $data['MRData']['RaceTable']['Races'][0]['PitStops'] ?? [];
            array_push($all, ...$stops);

            $offset += 100;

            if ($offset < (int) ($data['MRData']['total'] ?? 0)) {
                usleep(300_000);
            }
        } while ($offset < (int) ($data['MRData']['total'] ?? 0));

        return $all;
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function get(string $path, array $query = []): array
    {
        $response = Http::baseUrl($this->baseUrl)
            ->timeout(15)
            ->retry(3, 500)
            ->get($path, $query);

        if ($response->failed()) {
            throw new RuntimeException("Jolpica F1 API request failed: {$path} ({$response->status()})");
        }

        return $response->json() ?? [];
    }
}
