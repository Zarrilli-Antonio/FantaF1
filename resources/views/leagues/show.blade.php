<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-white leading-tight flex items-center gap-2">
                    <x-icon name="flag" class="w-5 h-5 text-f1red" /> {{ $league->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">{{ __('Stagione') }} {{ $league->season->year }}</p>
            </div>
            <span class="hidden sm:flex items-center gap-2 text-sm text-gray-400 font-mono bg-gray-900 border border-gray-800 rounded-md px-3 py-1.5">
                <x-icon name="link" class="w-4 h-4" /> {{ $league->invite_code }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            @if (session('status'))
                <div class="flex items-center gap-2 p-4 bg-green-900/30 border border-green-800 text-green-300 rounded-lg">
                    <x-icon name="check-circle" class="w-5 h-5 shrink-0" />
                    {{ session('status') }}
                </div>
            @endif

            @foreach ($errors->all() as $error)
                <div class="p-4 bg-red-900/30 border border-red-800 text-red-300 rounded-lg">{{ $error }}</div>
            @endforeach

            <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-5 flex items-center gap-4">
                <div class="h-11 w-11 rounded-full bg-f1red/10 text-f1red flex items-center justify-center shrink-0">
                    <x-icon name="clock" class="w-6 h-6" />
                </div>
                @if ($nextRace)
                    <div>
                        <p class="text-sm text-gray-400">{{ __('Prossima gara') }}</p>
                        <p class="text-base font-semibold text-white">{{ $nextRace->name }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $nextRace->starts_at->translatedFormat('d M Y, H:i') }}
                            &middot; {{ $nextRace->starts_at->diffForHumans() }}
                        </p>
                    </div>
                @else
                    <p class="text-sm text-gray-400">{{ __('Nessuna gara in programma per questa stagione.') }}</p>
                @endif
            </div>

            <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <x-icon name="users" class="w-5 h-5 text-f1red" /> {{ __('Membri') }}
                </h3>
                <ul class="divide-y divide-gray-800">
                    @foreach ($league->members as $member)
                        @php $pct = $league->budget > 0 ? max(0, min(100, round($member->budget_remaining / $league->budget * 100))) : 0; @endphp
                        <li class="py-3 flex items-center gap-4">
                            <div class="h-9 w-9 rounded-full bg-f1red/10 text-f1red flex items-center justify-center font-semibold text-sm shrink-0">
                                {{ strtoupper(substr($member->user->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-gray-200 truncate">{{ $member->user->name }}</span>
                                    <span class="text-xs text-gray-500 whitespace-nowrap flex items-center gap-1">
                                        <x-icon name="coin" class="w-3.5 h-3.5" /> {{ $member->budget_remaining }} / {{ $league->budget }}
                                    </span>
                                </div>
                                <div class="mt-1.5 h-1.5 w-full rounded-full bg-gray-800 overflow-hidden">
                                    <div class="h-full bg-f1red rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if ($league->auction_status === 'pending')
                <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-8 text-center">
                    <x-icon name="gavel" class="w-10 h-10 mx-auto text-gray-600 mb-4" />
                    <p class="text-gray-400 mb-6 max-w-md mx-auto">
                        {{ __('L\'asta non è ancora iniziata. Condividi il codice invito e avviala quando tutti i membri si sono uniti.') }}
                    </p>
                    @if ($league->owner_id === auth()->id())
                        <form method="POST" action="{{ route('leagues.start-auction', $league) }}">
                            @csrf
                            <x-primary-button>{{ __('Avvia asta') }}</x-primary-button>
                        </form>
                    @endif
                </div>
            @elseif ($league->auction_status === 'round_open')
                @if ($league->owner_id === auth()->id())
                    <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-4 flex items-center justify-between gap-4 flex-wrap">
                        <p class="text-sm text-gray-400">
                            {{ __('Come amministratore puoi chiudere il round in anticipo, senza aspettare la scadenza.') }}
                        </p>
                        <form method="POST" action="{{ route('leagues.close-round', $league) }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from(__("Chiudere subito il round d'asta corrente?")) }})">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-gray-700 text-gray-200 text-sm font-semibold hover:border-f1red hover:text-f1red transition">
                                <x-icon name="clock" class="w-4 h-4" /> {{ __('Chiudi round ora') }}
                            </button>
                        </form>
                    </div>
                @endif

                <livewire:auction-room :league="$league" />
            @else
                <livewire:race-predictions :league="$league" />

                <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <x-icon name="trophy" class="w-5 h-5 text-f1red" /> {{ __('Rose') }}
                    </h3>
                    <div class="grid gap-6 sm:grid-cols-2">
                        @foreach ($league->members as $member)
                            <div class="rounded-lg border border-gray-800 bg-gray-950 p-4">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="h-8 w-8 rounded-full bg-f1red/10 text-f1red flex items-center justify-center font-semibold text-xs shrink-0">
                                        {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                    </div>
                                    <p class="font-medium text-gray-100">{{ $member->user->name }}</p>
                                </div>
                                <ul class="space-y-1.5">
                                    @foreach ($league->rosterPicks->where('user_id', $member->user_id) as $pick)
                                        <li class="flex items-center justify-between text-sm">
                                            <span class="flex items-center gap-2 text-gray-300">
                                                <x-icon :name="$pick->pickable_type === 'driver' ? 'users' : 'flag'" class="w-4 h-4 text-gray-500" />
                                                @if ($pick->pickable_type === 'driver')
                                                    <a href="{{ route('drivers.show', $pick->pickable_id) }}" wire:navigate class="hover:text-f1red">{{ $pick->pickable()->full_name }}</a>
                                                @else
                                                    {{ $pick->pickable()->name }}
                                                @endif
                                            </span>
                                            <span class="text-gray-500 flex items-center gap-1">
                                                <x-icon name="coin" class="w-3.5 h-3.5" /> {{ $pick->price_paid }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>

                <livewire:league-standings :league="$league" />
            @endif

            @if ($league->owner_id === auth()->id())
                <div class="rounded-xl border border-red-900/60 bg-red-950/20 p-6 flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <h3 class="text-sm font-semibold text-red-300">{{ __('Elimina lega') }}</h3>
                        <p class="text-sm text-red-400/80 mt-1 max-w-md">
                            {{ __('Rimuove definitivamente la lega, l\'asta, le rose e i punteggi di tutti i membri. Azione irreversibile.') }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('leagues.destroy', $league) }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from(__("Eliminare definitivamente la lega ':name'? L'azione non è reversibile.", ['name' => $league->name])) }})">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-red-900/60 border border-red-800 text-red-200 text-sm font-semibold hover:bg-red-900 transition">
                            <x-icon name="trash" class="w-4 h-4" /> {{ __('Elimina lega') }}
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
