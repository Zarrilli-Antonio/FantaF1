<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\RaceLap;
use App\Models\RacePitStop;
use Illuminate\View\View;

class RaceController extends Controller
{
    public function show(Race $race): View
    {
        $race->load('season');

        $results = $race->results()
            ->with(['driver', 'constructor'])
            ->orderByRaw('position IS NULL, position ASC')
            ->get();

        $constructorStandings = $results
            ->groupBy('constructor_id')
            ->map(function ($rows) {
                return [
                    'constructor' => $rows->first()->constructor,
                    'points' => $rows->sum('real_points'),
                    'drivers' => $rows->map(fn ($r) => [
                        'name' => $r->driver->full_name,
                        'position' => $r->position,
                    ])->all(),
                ];
            })
            ->sortByDesc('points')
            ->values();

        $lapsByDriver = RaceLap::where('race_id', $race->id)
            ->orderBy('lap')
            ->get()
            ->groupBy('driver_id');

        $pitStopsByDriver = RacePitStop::where('race_id', $race->id)
            ->orderBy('stop')
            ->get()
            ->groupBy('driver_id');

        return view('races.show', [
            'race' => $race,
            'results' => $results,
            'constructorStandings' => $constructorStandings,
            'lapsByDriver' => $lapsByDriver,
            'pitStopsByDriver' => $pitStopsByDriver,
        ]);
    }
}
