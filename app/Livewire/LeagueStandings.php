<?php

namespace App\Livewire;

use App\Models\Bid;
use App\Models\Constructor;
use App\Models\Driver;
use App\Models\FantasyScore;
use App\Models\League;
use App\Models\Race;
use App\Models\RaceResult;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LeagueStandings extends Component
{
    /**
     * Fixed categorical order (validated for CVD/contrast against the app's
     * dark surface) — assigned by join order so a color always identifies the
     * same user, never a standings rank.
     */
    private const SERIES_COLORS = [
        '#3987e5', '#d95926', '#199e70', '#c98500',
        '#d55181', '#008300', '#9085e9', '#e66767',
    ];

    public League $league;

    public function mount(League $league): void
    {
        $this->league = $league;
    }

    #[Computed]
    public function standings()
    {
        $colorByUser = $this->membersWithColor()->mapWithKeys(fn ($row) => [$row['member']->user_id => $row['color']]);

        return FantasyScore::where('league_id', $this->league->id)
            ->selectRaw('user_id, sum(points) as total_points, count(*) as races_scored')
            ->groupBy('user_id')
            ->with('user')
            ->orderByDesc('total_points')
            ->get()
            ->map(function ($row) use ($colorByUser) {
                $row->color = $colorByUser->get($row->user_id, '#3987e5');

                return $row;
            });
    }

    private function membersWithColor()
    {
        return $this->league->members()->with('user')->orderBy('id')->get()->values()
            ->map(fn ($member, $index) => [
                'member' => $member,
                'color' => self::SERIES_COLORS[$index % count(self::SERIES_COLORS)],
            ]);
    }

    /**
     * Cumulative points per race per user, for the progression chart.
     *
     * @return array{races: array<int, array{round: int, label: string, name: string}>, series: array<int, array{user_id: int, name: string, color: string, values: array<int, float>, total: float}>}
     */
    #[Computed]
    public function chartSeries(): array
    {
        $races = Race::where('season_id', $this->league->season_id)
            ->where('status', 'completed')
            ->orderBy('round')
            ->get(['id', 'round', 'name']);

        $scoresByUser = FantasyScore::where('league_id', $this->league->id)
            ->get()
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->keyBy('race_id'));

        $series = $this->membersWithColor()->map(function ($row) use ($races, $scoresByUser) {
            $member = $row['member'];
            $rows = $scoresByUser->get($member->user_id, collect());
            $cumulative = 0;
            $values = $races->map(function ($race) use ($rows, &$cumulative) {
                $cumulative += (float) ($rows->get($race->id)?->points ?? 0);

                return $cumulative;
            })->all();

            return [
                'user_id' => $member->user_id,
                'name' => $member->user->name,
                'color' => $row['color'],
                'values' => $values,
                'total' => $values[array_key_last($values)] ?? 0.0,
            ];
        })->all();

        return [
            'races' => $races->map(fn ($race) => [
                'round' => $race->round,
                'label' => 'R'.$race->round,
                'name' => $race->name,
            ])->all(),
            'series' => $series,
        ];
    }

    /**
     * Per-race, per-user breakdown of exactly which driver/team scored what —
     * so it's clear who earned points and who lost them, race by race.
     *
     * @return array<int, array{race: array, members: array<int, array>}>
     */
    #[Computed]
    public function raceBreakdowns(): array
    {
        $races = Race::where('season_id', $this->league->season_id)
            ->where('status', 'completed')
            ->orderByDesc('round')
            ->get(['id', 'round', 'name']);

        $membersWithColor = $this->membersWithColor();

        $scoresByRace = FantasyScore::where('league_id', $this->league->id)
            ->whereIn('race_id', $races->pluck('id'))
            ->get()
            ->groupBy('race_id');

        $driverIds = collect();
        $constructorIds = collect();
        foreach ($scoresByRace as $raceScores) {
            foreach ($raceScores as $score) {
                $breakdown = $score->breakdown ?? [];
                foreach ($breakdown['drivers'] ?? [] as $entry) {
                    $driverIds->push($entry['driver_id']);
                }
                foreach ($breakdown['constructors'] ?? [] as $entry) {
                    $constructorIds->push($entry['constructor_id']);
                }
                foreach ($breakdown['predictions'] ?? [] as $entry) {
                    if ($entry['pickable_type'] === Bid::TYPE_DRIVER) {
                        $driverIds->push($entry['pickable_id']);
                    } else {
                        $constructorIds->push($entry['pickable_id']);
                    }
                }
            }
        }

        $drivers = Driver::whereIn('id', $driverIds->unique())->get()->keyBy('id');
        $constructors = Constructor::whereIn('id', $constructorIds->unique())->get()->keyBy('id');

        $resultsByRace = RaceResult::whereIn('race_id', $races->pluck('id'))
            ->get()
            ->groupBy('race_id')
            ->map(fn ($rows) => $rows->keyBy('driver_id'));

        return $races->map(function ($race) use ($scoresByRace, $membersWithColor, $drivers, $constructors, $resultsByRace) {
            $raceScores = $scoresByRace->get($race->id, collect())->keyBy('user_id');
            $raceResults = $resultsByRace->get($race->id, collect());

            $memberRows = $membersWithColor->map(function ($row) use ($raceScores, $drivers, $constructors, $raceResults) {
                $member = $row['member'];
                $score = $raceScores->get($member->user_id);
                $breakdown = $score->breakdown ?? ['drivers' => [], 'constructors' => []];

                $formatDriver = fn ($entry) => $this->formatDriverEntry($entry, $drivers, $raceResults);
                $formatConstructor = fn ($entry) => $this->formatConstructorEntry($entry, $constructors);
                $formatPrediction = fn ($entry) => $this->formatPredictionEntry($entry, $drivers, $constructors);

                return [
                    'user_id' => $member->user_id,
                    'name' => $member->user->name,
                    'color' => $row['color'],
                    'total' => $score->points ?? 0,
                    'drivers' => collect($breakdown['drivers'] ?? [])->map($formatDriver)->all(),
                    'constructors' => collect($breakdown['constructors'] ?? [])->map($formatConstructor)->all(),
                    'predictions' => collect($breakdown['predictions'] ?? [])->map($formatPrediction)->all(),
                ];
            })->all();

            return [
                'race' => ['id' => $race->id, 'round' => $race->round, 'name' => $race->name],
                'members' => $memberRows,
            ];
        })->all();
    }

    /**
     * @return array{name: string, points: float, detail: string}
     */
    private function formatDriverEntry(array $entry, $drivers, $raceResults): array
    {
        $driver = $drivers->get($entry['driver_id']);
        $result = $raceResults->get($entry['driver_id']);
        $detail = $entry['detail'] ?? [];

        if (($detail['reason'] ?? null) === 'no_result') {
            $parts = [__('Non ha corso')];
        } else {
            $parts = [];
            if ($result?->position) {
                $parts[] = "P{$result->position} " . $this->signed($detail['position_points'] ?? 0);
            }
            if (isset($detail['pole_bonus'])) {
                $parts[] = __('Pole') . ' ' . $this->signed($detail['pole_bonus']);
            }
            if (isset($detail['fastest_lap_bonus'])) {
                $parts[] = __('Giro veloce') . ' ' . $this->signed($detail['fastest_lap_bonus']);
            }
            if (isset($detail['podium_bonus'])) {
                $parts[] = __('Podio') . ' ' . $this->signed($detail['podium_bonus']);
            }
            if (isset($detail['dnf_penalty'])) {
                $parts[] = __('Ritiro') . ' ' . $this->signed($detail['dnf_penalty']);
            }
        }

        return [
            'name' => $driver?->full_name ?? '—',
            'points' => $entry['points'] ?? 0,
            'detail' => implode(' · ', $parts),
        ];
    }

    /**
     * @return array{name: string, points: float, detail: string}
     */
    private function formatConstructorEntry(array $entry, $constructors): array
    {
        $constructor = $constructors->get($entry['constructor_id']);
        $detail = $entry['detail'] ?? [];

        return [
            'name' => $constructor?->name ?? '—',
            'points' => $entry['points'] ?? 0,
            'detail' => isset($detail['real_points']) ? __('Punti reali gara: :points', ['points' => $detail['real_points']]) : '',
        ];
    }

    /**
     * @return array{type: string, label: string, name: string, correct: bool, points: float}
     */
    private function formatPredictionEntry(array $entry, $drivers, $constructors): array
    {
        $name = $entry['pickable_type'] === Bid::TYPE_DRIVER
            ? $drivers->get($entry['pickable_id'])?->full_name
            : $constructors->get($entry['pickable_id'])?->name;

        $labels = [
            'pole' => __('Pole position'),
            'dnf' => __('Primo ritiro (DNF)'),
            'fastest_pit_stop' => __('Pit stop più veloce'),
        ];

        return [
            'type' => $entry['type'],
            'label' => $labels[$entry['type']] ?? $entry['type'],
            'name' => $name ?? '—',
            'correct' => (bool) $entry['correct'],
            'points' => $entry['points'] ?? 0,
        ];
    }

    private function signed(float|int $value): string
    {
        return ($value >= 0 ? '+' : '').rtrim(rtrim(number_format($value, 1), '0'), '.');
    }

    public function render()
    {
        return view('livewire.league-standings');
    }
}
