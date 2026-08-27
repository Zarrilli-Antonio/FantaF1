<?php

namespace App\Livewire;

use App\Models\Bid;
use App\Models\Constructor;
use App\Models\Driver;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\RosterPick;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AuctionRoom extends Component
{
    public League $league;

    public string $search = '';

    public array $bidAmounts = [];

    public ?string $error = null;

    public function mount(League $league): void
    {
        $this->league = $league;
    }

    #[Computed]
    public function currentRound()
    {
        return $this->league->currentAuctionRound();
    }

    #[Computed]
    public function member(): ?LeagueMember
    {
        return LeagueMember::where('league_id', $this->league->id)
            ->where('user_id', Auth::id())
            ->first();
    }

    #[Computed]
    public function myBids()
    {
        if (! $this->currentRound) {
            return collect();
        }

        return Bid::where('auction_round_id', $this->currentRound->id)
            ->where('user_id', Auth::id())
            ->get()
            ->keyBy(fn ($bid) => "{$bid->pickable_type}:{$bid->pickable_id}");
    }

    #[Computed]
    public function leadingBids()
    {
        if (! $this->currentRound) {
            return collect();
        }

        return Bid::where('auction_round_id', $this->currentRound->id)
            ->with('user')
            ->orderByDesc('amount')
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($bid) => "{$bid->pickable_type}:{$bid->pickable_id}")
            ->map(fn ($bids) => $bids->first());
    }

    #[Computed]
    public function assignedItemKeys()
    {
        return RosterPick::where('league_id', $this->league->id)
            ->get()
            ->map(fn ($pick) => "{$pick->pickable_type}:{$pick->pickable_id}")
            ->flip();
    }

    #[Computed]
    public function availableDrivers()
    {
        return Driver::query()
            ->when($this->search, fn ($q) => $q->where('last_name', 'like', "%{$this->search}%"))
            ->get()
            ->reject(fn ($driver) => $this->assignedItemKeys->has("driver:{$driver->id}"))
            ->values();
    }

    #[Computed]
    public function availableConstructors()
    {
        return Constructor::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->get()
            ->reject(fn ($c) => $this->assignedItemKeys->has("constructor:{$c->id}"))
            ->values();
    }

    #[Computed]
    public function committedTotal(): int
    {
        return (int) $this->myBids->sum('amount');
    }

    #[Computed]
    public function availableBudget(): int
    {
        return max(0, ($this->member?->budget_remaining ?? 0) - $this->committedTotal);
    }

    private function availableBudgetExcluding(string $itemKey): int
    {
        $committedOnOtherItems = $this->myBids->except([$itemKey])->sum('amount');

        return max(0, ($this->member?->budget_remaining ?? 0) - $committedOnOtherItems);
    }

    #[Computed]
    public function driverSlotsFilled(): int
    {
        return $this->member?->rosterSlotsFilled(Bid::TYPE_DRIVER) ?? 0;
    }

    #[Computed]
    public function constructorSlotsFilled(): int
    {
        return $this->member?->rosterSlotsFilled(Bid::TYPE_CONSTRUCTOR) ?? 0;
    }

    public function placeBid(string $type, int $pickableId): void
    {
        $this->error = null;
        $round = $this->currentRound;
        $member = $this->member;

        if (! $round || ! $member) {
            $this->error = __("Nessun round d'asta attivo.");

            return;
        }

        $amount = (int) ($this->bidAmounts["{$type}:{$pickableId}"] ?? 0);

        if ($amount < 1) {
            $this->error = __('Inserisci un importo valido.');

            return;
        }

        $itemKey = "{$type}:{$pickableId}";
        $available = $this->availableBudgetExcluding($itemKey);

        if ($amount > $available) {
            $this->error = __('Offerta superiore ai crediti disponibili (:available, considerando le tue altre offerte in corso).', ['available' => $available]);

            return;
        }

        $leadingBid = $this->leadingBids->get($itemKey);
        if ($leadingBid && $leadingBid->user_id !== Auth::id() && $amount <= $leadingBid->amount) {
            $this->error = __('Devi offrire più di :amount crediti per superare :name.', ['amount' => $leadingBid->amount, 'name' => $leadingBid->user->name]);

            return;
        }

        $slotLimit = $type === Bid::TYPE_DRIVER ? $this->league->driver_slots : $this->league->constructor_slots;
        $slotsFilled = $type === Bid::TYPE_DRIVER ? $this->driverSlotsFilled : $this->constructorSlotsFilled;

        $alreadyBidding = $this->myBids->has("{$type}:{$pickableId}");
        if (! $alreadyBidding && $slotsFilled >= $slotLimit) {
            $this->error = __('Hai già riempito tutti gli slot per questo tipo.');

            return;
        }

        Bid::updateOrCreate(
            [
                'auction_round_id' => $round->id,
                'user_id' => Auth::id(),
                'pickable_type' => $type,
                'pickable_id' => $pickableId,
            ],
            [
                'league_id' => $this->league->id,
                'amount' => $amount,
            ],
        );

        unset($this->bidAmounts["{$type}:{$pickableId}"]);
    }

    public function removeBid(string $type, int $pickableId): void
    {
        $round = $this->currentRound;

        if (! $round) {
            return;
        }

        Bid::where('auction_round_id', $round->id)
            ->where('user_id', Auth::id())
            ->where('pickable_type', $type)
            ->where('pickable_id', $pickableId)
            ->delete();
    }

    public function render()
    {
        return view('livewire.auction-room');
    }
}
