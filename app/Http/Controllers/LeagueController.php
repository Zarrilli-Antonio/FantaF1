<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Models\LeagueMember;
use App\Models\Race;
use App\Models\Season;
use App\Services\Auction\AuctionResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LeagueController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $leagues = League::whereHas('members', fn ($q) => $q->where('user_id', $user->id))
            ->with('season')
            ->get();

        $seasons = Season::orderByDesc('year')->get();

        return view('leagues.index', compact('leagues', 'seasons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'season_id' => ['required', 'exists:seasons,id'],
            'driver_slots' => ['required', 'integer', 'min:1', 'max:10'],
            'constructor_slots' => ['required', 'integer', 'min:0', 'max:5'],
            'budget' => ['required', 'integer', 'min:100'],
            'prediction_points' => ['nullable', 'array'],
            'prediction_points.pole' => ['nullable', 'integer', 'min:0'],
            'prediction_points.dnf' => ['nullable', 'integer', 'min:0'],
            'prediction_points.fastest_pit_stop' => ['nullable', 'integer', 'min:0'],
        ]);

        if (! empty($data['prediction_points'])) {
            $data['prediction_points'] = array_map('intval', $data['prediction_points']);
        }

        $league = League::create([
            ...$data,
            'owner_id' => Auth::id(),
            'invite_code' => Str::upper(Str::random(8)),
        ]);

        LeagueMember::create([
            'league_id' => $league->id,
            'user_id' => Auth::id(),
            'budget_remaining' => $league->budget,
            'joined_at' => now(),
        ]);

        return redirect()->route('leagues.show', $league)
            ->with('status', __('Lega creata. Condividi il codice invito con gli amici.'));
    }

    public function join(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'invite_code' => ['required', 'string'],
        ]);

        $league = League::where('invite_code', Str::upper($data['invite_code']))->firstOrFail();

        if ($league->auction_status !== 'pending') {
            return back()->withErrors(['invite_code' => __("L'asta di questa lega è già iniziata.")]);
        }

        LeagueMember::firstOrCreate(
            ['league_id' => $league->id, 'user_id' => Auth::id()],
            ['budget_remaining' => $league->budget, 'joined_at' => now()],
        );

        return redirect()->route('leagues.show', $league)->with('status', __('Ti sei unito alla lega!'));
    }

    public function show(League $league): View
    {
        $this->authorizeMember($league);

        $league->load(['members.user', 'season', 'rosterPicks']);

        $nextRace = Race::where('season_id', $league->season_id)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->first();

        return view('leagues.show', [
            'league' => $league,
            'currentRound' => $league->currentAuctionRound(),
            'nextRace' => $nextRace,
        ]);
    }

    public function startAuction(League $league): RedirectResponse
    {
        $this->authorizeOwner($league);

        if ($league->auction_status !== 'pending') {
            return back()->withErrors(['auction' => __("L'asta è già stata avviata.")]);
        }

        if ($league->members()->count() < 2) {
            return back()->withErrors(['auction' => __("Servono almeno 2 membri per avviare l'asta.")]);
        }

        $durationHours = config('fantasy.auction_round_duration_hours', 48);

        $league->auctionRounds()->create([
            'round_number' => 1,
            'opens_at' => now(),
            'closes_at' => now()->addHours($durationHours),
            'status' => 'open',
        ]);

        $league->update(['auction_status' => 'round_open']);

        return redirect()->route('leagues.show', $league)->with('status', __('Asta avviata!'));
    }

    public function closeRound(League $league, AuctionResolver $resolver): RedirectResponse
    {
        $this->authorizeOwner($league);

        $round = $league->currentAuctionRound();

        if (! $round) {
            return back()->withErrors(['auction' => __("Nessun round d'asta attivo da chiudere.")]);
        }

        $resolver->resolve($round);

        $message = $league->fresh()->auction_status === 'completed'
            ? __('Asta chiusa: le rose sono ora bloccate per la stagione.')
            : __('Round chiuso. È stato aperto un nuovo round per gli slot rimasti liberi.');

        return redirect()->route('leagues.show', $league)->with('status', $message);
    }

    public function destroy(League $league): RedirectResponse
    {
        $this->authorizeOwner($league);

        $league->delete();

        return redirect()->route('leagues.index')->with('status', __('Lega ":name" eliminata.', ['name' => $league->name]));
    }

    private function authorizeMember(League $league): void
    {
        $isMember = $league->members()->where('user_id', Auth::id())->exists();

        abort_unless($isMember, 403);
    }

    private function authorizeOwner(League $league): void
    {
        $this->authorizeMember($league);

        abort_unless($league->owner_id === Auth::id(), 403);
    }
}
