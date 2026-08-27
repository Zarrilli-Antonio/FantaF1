<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Le tue leghe') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            @if (session('status'))
                <div class="flex items-center gap-2 p-4 bg-green-900/30 border border-green-800 text-green-300 rounded-lg">
                    <x-icon name="check-circle" class="w-5 h-5 shrink-0" />
                    {{ session('status') }}
                </div>
            @endif

            <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <x-icon name="trophy" class="w-5 h-5 text-f1red" /> {{ __('Le mie leghe') }}
                </h3>

                @if ($leagues->isEmpty())
                    <div class="text-center py-10">
                        <x-icon name="flag" class="w-10 h-10 mx-auto text-gray-600 mb-3" />
                        <p class="text-gray-400">{{ __('Non fai ancora parte di nessuna lega. Creane una o chiedi un codice invito a un amico.') }}</p>
                    </div>
                @else
                    <ul class="divide-y divide-gray-800">
                        @foreach ($leagues as $league)
                            <li class="py-4 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="h-10 w-10 rounded-full bg-f1red/10 text-f1red flex items-center justify-center shrink-0">
                                        <x-icon name="flag" class="w-5 h-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('leagues.show', $league) }}" wire:navigate class="font-medium text-gray-100 hover:text-f1red truncate block">
                                            {{ $league->name }}
                                        </a>
                                        <p class="text-sm text-gray-500">
                                            {{ __('Stagione') }} {{ $league->season->year }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <span class="text-xs px-2.5 py-1 rounded-full border border-gray-700 text-gray-400 whitespace-nowrap">
                                        {{ match($league->auction_status) {
                                            'pending' => __('In attesa'),
                                            'round_open' => __('Asta in corso'),
                                            default => __('Completata'),
                                        } }}
                                    </span>
                                    <span class="hidden sm:flex items-center gap-1 text-xs text-gray-500 font-mono">
                                        <x-icon name="link" class="w-3.5 h-3.5" /> {{ $league->invite_code }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                    <h3 class="text-lg font-semibold text-white mb-1 flex items-center gap-2">
                        <x-icon name="plus-circle" class="w-5 h-5 text-f1red" /> {{ __('Crea una nuova lega') }}
                    </h3>
                    <p class="text-sm text-gray-500 mb-6">{{ __('Imposta le regole della tua lega privata e invita i tuoi amici.') }}</p>

                    <form method="POST" action="{{ route('leagues.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Nome lega')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="season_id" :value="__('Stagione')" />
                            <select id="season_id" name="season_id" class="mt-1 block w-full rounded-md border-gray-700 bg-gray-950 text-gray-200 focus:border-f1red focus:ring-f1red" required>
                                @foreach ($seasons as $season)
                                    <option value="{{ $season->id }}">{{ $season->year }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('season_id')" class="mt-1" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="driver_slots" :value="__('Piloti in rosa')" />
                                <x-text-input id="driver_slots" name="driver_slots" type="number" min="1" max="10" value="5" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="constructor_slots" :value="__('Team in rosa')" />
                                <x-text-input id="constructor_slots" name="constructor_slots" type="number" min="0" max="5" value="1" class="mt-1 block w-full" required />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="budget" :value="__('Budget crediti')" />
                            <x-text-input id="budget" name="budget" type="number" min="100" value="1000" class="mt-1 block w-full" required />
                        </div>

                        <details class="rounded-md border border-gray-800 bg-gray-950/60 px-4 py-3">
                            <summary class="text-sm font-medium text-gray-300 cursor-pointer">
                                {{ __('Punti pronostico (avanzato)') }}
                            </summary>
                            <p class="text-xs text-gray-500 mt-2 mb-3">
                                {{ __('Punti assegnati a chi indovina il pronostico prima di ogni gara. Ogni tipo di pronostico è indipendente.') }}
                            </p>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <x-input-label for="prediction_points_pole" :value="__('Pole position')" />
                                    <x-text-input id="prediction_points_pole" name="prediction_points[pole]" type="number" min="0" value="{{ config('fantasy.prediction_points.pole') }}" class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <x-input-label for="prediction_points_dnf" :value="__('Primo ritiro')" />
                                    <x-text-input id="prediction_points_dnf" name="prediction_points[dnf]" type="number" min="0" value="{{ config('fantasy.prediction_points.dnf') }}" class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <x-input-label for="prediction_points_fastest_pit_stop" :value="__('Pit stop veloce')" />
                                    <x-text-input id="prediction_points_fastest_pit_stop" name="prediction_points[fastest_pit_stop]" type="number" min="0" value="{{ config('fantasy.prediction_points.fastest_pit_stop') }}" class="mt-1 block w-full" required />
                                </div>
                            </div>
                        </details>

                        <x-primary-button class="w-full justify-center">{{ __('Crea lega') }}</x-primary-button>
                    </form>
                </div>

                <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                    <h3 class="text-lg font-semibold text-white mb-1 flex items-center gap-2">
                        <x-icon name="link" class="w-5 h-5 text-f1red" /> {{ __('Unisciti a una lega') }}
                    </h3>
                    <p class="text-sm text-gray-500 mb-6">{{ __('Hai ricevuto un codice invito da un amico? Inseriscilo qui.') }}</p>

                    <form method="POST" action="{{ route('leagues.join') }}" class="space-y-4">
                        @csrf

                        <div>
                            <x-input-label for="invite_code" :value="__('Codice invito')" />
                            <x-text-input id="invite_code" name="invite_code" type="text" class="mt-1 block w-full uppercase" required />
                            <x-input-error :messages="$errors->get('invite_code')" class="mt-1" />
                        </div>

                        <x-primary-button class="w-full justify-center">{{ __('Unisciti') }}</x-primary-button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
