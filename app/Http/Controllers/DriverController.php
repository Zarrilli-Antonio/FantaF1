<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function show(Driver $driver): View
    {
        $driver->load('constructor');

        $results = $driver->raceResults()
            ->with(['race', 'constructor'])
            ->whereHas('race', fn ($q) => $q->where('status', 'completed'))
            ->get()
            ->sortByDesc(fn ($r) => $r->race->starts_at);

        $stats = [
            'races' => $results->count(),
            'wins' => $results->where('position', 1)->count(),
            'podiums' => $results->filter(fn ($r) => $r->position !== null && $r->position <= 3)->count(),
            'poles' => $results->where('grid', 1)->count(),
            'fastest_laps' => $results->where('fastest_lap', true)->count(),
            'dnfs' => $results->filter(fn ($r) => $r->isDnf())->count(),
            'points' => $results->sum('real_points'),
            'best_position' => $results->pluck('position')->filter()->min(),
        ];

        return view('drivers.show', [
            'driver' => $driver,
            'results' => $results->values(),
            'stats' => $stats,
        ]);
    }
}
