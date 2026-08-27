<?php

namespace App\Livewire;

use App\Models\Constructor;
use App\Models\Driver;
use App\Models\League;
use App\Models\Race;
use App\Models\RacePrediction;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RacePredictions extends Component
{
    public League $league;

    public string $driverSearch = '';

    public string $constructorSearch = '';

    public ?string $error = null;

    public function mount(League $league): void
    {
        $this->league = $league;
    }

    /**
     * The next race of the season that hasn't started yet: predictions can
     * only be made for this one, and only until it starts.
     */
    #[Computed]
    public function race(): ?Race
    {
        return Race::where('season_id', $this->league->season_id)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->first();
    }

    #[Computed]
    public function locked(): bool
    {
        return ! $this->race;
    }

    #[Computed]
    public function myPredictions()
    {
        if (! $this->race) {
            return collect();
        }

        return RacePrediction::where('league_id', $this->league->id)
            ->where('user_id', Auth::id())
            ->where('race_id', $this->race->id)
            ->get()
            ->keyBy('type');
    }

    #[Computed]
    public function availableDrivers()
    {
        return Driver::query()
            ->when($this->driverSearch, fn ($q) => $q->where('last_name', 'like', "%{$this->driverSearch}%"))
            ->orderBy('last_name')
            ->get();
    }

    #[Computed]
    public function availableConstructors()
    {
        return Constructor::query()
            ->when($this->constructorSearch, fn ($q) => $q->where('name', 'like', "%{$this->constructorSearch}%"))
            ->orderBy('name')
            ->get();
    }

    public function predict(string $type, string $pickableType, int $pickableId): void
    {
        $this->error = null;

        if (! $this->race) {
            $this->error = __('Nessuna gara in programma su cui pronosticare.');

            return;
        }

        RacePrediction::updateOrCreate(
            [
                'league_id' => $this->league->id,
                'user_id' => Auth::id(),
                'race_id' => $this->race->id,
                'type' => $type,
            ],
            [
                'pickable_type' => $pickableType,
                'pickable_id' => $pickableId,
            ],
        );
    }

    public function removePrediction(string $type): void
    {
        if (! $this->race) {
            return;
        }

        RacePrediction::where('league_id', $this->league->id)
            ->where('user_id', Auth::id())
            ->where('race_id', $this->race->id)
            ->where('type', $type)
            ->delete();
    }

    public function render()
    {
        return view('livewire.race-predictions');
    }
}
