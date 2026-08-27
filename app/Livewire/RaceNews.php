<?php

namespace App\Livewire;

use App\Models\Race;
use App\Models\Season;
use App\Services\News\RaceRecapBuilder;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RaceNews extends Component
{
    #[Computed]
    public function season(): ?Season
    {
        return Season::where('is_active', true)->first();
    }

    #[Computed]
    public function hasLiveRace(): bool
    {
        if (! $this->season) {
            return false;
        }

        return Race::where('season_id', $this->season->id)
            ->where('status', '!=', 'completed')
            ->where('starts_at', '<=', now())
            ->exists();
    }

    /**
     * News feed: the live race (if any) pinned at the top, followed by
     * completed races in reverse chronological order. Future rounds aren't
     * "news" yet, so they stay out of the feed (the league page already
     * surfaces the next race).
     *
     * @return array<int, array{race: Race, state: string, recap: ?array}>
     */
    #[Computed]
    public function feed(): array
    {
        if (! $this->season) {
            return [];
        }

        $builder = app(RaceRecapBuilder::class);

        $liveRace = Race::where('season_id', $this->season->id)
            ->where('status', '!=', 'completed')
            ->where('starts_at', '<=', now())
            ->orderBy('starts_at')
            ->first();

        $completedRaces = Race::where('season_id', $this->season->id)
            ->where('status', 'completed')
            ->orderByDesc('round')
            ->get();

        $entries = collect();

        if ($liveRace) {
            $entries->push(['race' => $liveRace, 'state' => 'live', 'recap' => null]);
        }

        foreach ($completedRaces as $race) {
            $entries->push(['race' => $race, 'state' => 'completed', 'recap' => $builder->build($race)]);
        }

        return $entries->all();
    }

    public function render()
    {
        return view('livewire.race-news');
    }
}
