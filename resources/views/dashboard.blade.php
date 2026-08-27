<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $leagues = auth()->user()->leagueMemberships()->with('league.season')->get()->pluck('league');
        $activeSeason = \App\Models\Season::where('is_active', true)->first();
        $nextRace = $activeSeason
            ? \App\Models\Race::where('season_id', $activeSeason->id)->where('starts_at', '>=', now())->orderBy('starts_at')->first()
            : null;
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div class="rounded-xl border border-gray-800 bg-gradient-to-br from-gray-900 to-gray-900/40 p-8">
                <p class="text-f1red font-semibold text-sm uppercase tracking-wide mb-1">{{ __('Bentornato') }}</p>
                <h1 class="text-2xl font-bold text-white">{{ auth()->user()->name }}</h1>
                <p class="mt-2 text-gray-400 max-w-xl">
                    {{ __("Gestisci le tue leghe, segui l'asta e controlla la classifica: tutto quello che serve per la tua stagione di Fanta F1.") }}
                </p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6 flex items-start gap-4">
                    <div class="h-11 w-11 rounded-full bg-f1red/10 text-f1red flex items-center justify-center shrink-0">
                        <x-icon name="users" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">{{ __('Le tue leghe') }}</p>
                        <p class="text-2xl font-bold text-white">{{ $leagues->count() }}</p>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6 flex items-start gap-4">
                    <div class="h-11 w-11 rounded-full bg-f1red/10 text-f1red flex items-center justify-center shrink-0">
                        <x-icon name="flag" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">{{ __('Stagione attiva') }}</p>
                        <p class="text-2xl font-bold text-white">{{ $activeSeason?->year ?? '—' }}</p>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6 flex items-start gap-4">
                    <div class="h-11 w-11 rounded-full bg-f1red/10 text-f1red flex items-center justify-center shrink-0">
                        <x-icon name="clock" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">{{ __('Prossima gara') }}</p>
                        <p class="text-lg font-semibold text-white">{{ $nextRace?->name ?? __('Nessuna gara in programma') }}</p>
                        @if ($nextRace)
                            <p class="text-xs text-gray-500">{{ $nextRace->starts_at->translatedFormat('d M Y, H:i') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white">{{ __('Le tue leghe') }}</h3>
                    <a href="{{ route('leagues.index') }}" wire:navigate class="text-sm text-f1red hover:underline flex items-center gap-1">
                        {{ __('Vedi tutte') }} <x-icon name="chevron-right" class="w-4 h-4" />
                    </a>
                </div>

                @if ($leagues->isEmpty())
                    <div class="text-center py-10">
                        <x-icon name="flag" class="w-10 h-10 mx-auto text-gray-600 mb-3" />
                        <p class="text-gray-400 mb-4">{{ __('Non fai ancora parte di nessuna lega.') }}</p>
                        <a href="{{ route('leagues.index') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-f1red text-white text-sm font-semibold hover:bg-f1red-dark transition">
                            <x-icon name="plus-circle" class="w-4 h-4" /> {{ __('Crea la tua prima lega') }}
                        </a>
                    </div>
                @else
                    <ul class="divide-y divide-gray-800">
                        @foreach ($leagues as $league)
                            <li class="py-3 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-f1red/10 text-f1red flex items-center justify-center">
                                        <x-icon name="flag" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <a href="{{ route('leagues.show', $league) }}" wire:navigate class="font-medium text-gray-100 hover:text-f1red">
                                            {{ $league->name }}
                                        </a>
                                        <p class="text-xs text-gray-500">{{ __('Stagione') }} {{ $league->season->year }}</p>
                                    </div>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full border border-gray-700 text-gray-400">
                                    {{ match($league->auction_status) {
                                        'pending' => __('In attesa'),
                                        'round_open' => __('Asta in corso'),
                                        default => __('Completata'),
                                    } }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
