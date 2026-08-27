<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ url()->previous() }}" class="text-sm text-gray-400 hover:text-f1red flex items-center gap-1 mb-2">
                <x-icon name="chevron-right" class="w-3.5 h-3.5 rotate-180" /> {{ __('Torna indietro') }}
            </a>
            <h2 class="font-semibold text-xl text-white leading-tight flex items-center gap-2">
                <x-icon name="users" class="w-5 h-5 text-f1red" /> {{ $driver->full_name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                <div class="flex items-start gap-5 flex-wrap">
                    <div class="h-16 w-16 rounded-full bg-f1red/10 text-f1red flex items-center justify-center font-bold text-2xl shrink-0">
                        {{ $driver->number ?? strtoupper(substr($driver->first_name, 0, 1) . substr($driver->last_name, 0, 1)) }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h3 class="text-lg font-semibold text-white">{{ $driver->full_name }}</h3>
                            @if ($driver->code)
                                <span class="text-xs font-mono bg-gray-950 border border-gray-800 rounded-md px-2 py-0.5 text-gray-400">{{ $driver->code }}</span>
                            @endif
                            @if ($driver->number)
                                <span class="text-xs font-mono bg-gray-950 border border-gray-800 rounded-md px-2 py-0.5 text-gray-400">#{{ $driver->number }}</span>
                            @endif
                        </div>

                        <dl class="grid gap-3 sm:grid-cols-2 mt-3 text-sm">
                            @if ($driver->constructor)
                                <div class="flex items-center gap-2">
                                    <dt class="text-gray-500 flex items-center gap-1.5"><x-icon name="flag" class="w-4 h-4" /> {{ __('Team attuale') }}</dt>
                                    <dd class="text-gray-200">{{ $driver->constructor->name }}</dd>
                                </div>
                            @endif
                            @if ($driver->nationality)
                                <div class="flex items-center gap-2">
                                    <dt class="text-gray-500 flex items-center gap-1.5"><x-icon name="flag" class="w-4 h-4" /> {{ __('Nazionalità') }}</dt>
                                    <dd class="text-gray-200">{{ $driver->flag }} {{ $driver->nationality }}</dd>
                                </div>
                            @endif
                            @if ($driver->date_of_birth)
                                <div class="flex items-center gap-2">
                                    <dt class="text-gray-500 flex items-center gap-1.5"><x-icon name="clock" class="w-4 h-4" /> {{ __('Data di nascita') }}</dt>
                                    <dd class="text-gray-200">{{ $driver->date_of_birth->translatedFormat('d M Y') }} ({{ $driver->age }} {{ __('anni') }})</dd>
                                </div>
                            @endif
                            @if ($driver->wikipedia_url)
                                <div class="flex items-center gap-2">
                                    <dt class="text-gray-500 flex items-center gap-1.5"><x-icon name="link" class="w-4 h-4" /> {{ __('Approfondisci') }}</dt>
                                    <dd><a href="{{ $driver->wikipedia_url }}" target="_blank" rel="noopener" class="text-f1red hover:underline">Wikipedia</a></dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <x-icon name="trophy" class="w-5 h-5 text-f1red" /> {{ __('Statistiche') }}
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="rounded-lg border border-gray-800 bg-gray-950 p-4 text-center">
                        <p class="text-2xl font-bold text-white">{{ $stats['races'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Gare') }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-800 bg-gray-950 p-4 text-center">
                        <p class="text-2xl font-bold text-white">{{ $stats['wins'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Vittorie') }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-800 bg-gray-950 p-4 text-center">
                        <p class="text-2xl font-bold text-white">{{ $stats['podiums'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Podi') }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-800 bg-gray-950 p-4 text-center">
                        <p class="text-2xl font-bold text-white">{{ $stats['poles'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Pole') }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-800 bg-gray-950 p-4 text-center">
                        <p class="text-2xl font-bold text-white">{{ $stats['fastest_laps'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Giri veloci') }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-800 bg-gray-950 p-4 text-center">
                        <p class="text-2xl font-bold text-white">{{ $stats['dnfs'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Ritiri') }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-800 bg-gray-950 p-4 text-center">
                        <p class="text-2xl font-bold text-white">{{ rtrim(rtrim(number_format($stats['points'], 1), '0'), '.') }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Punti reali') }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-800 bg-gray-950 p-4 text-center">
                        <p class="text-2xl font-bold text-white">{{ $stats['best_position'] ? 'P'.$stats['best_position'] : '—' }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Miglior risultato') }}</p>
                    </div>
                </div>
            </div>

            @if ($results->isNotEmpty())
                <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <x-icon name="flag" class="w-5 h-5 text-f1red" /> {{ __('Storico gare') }}
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-gray-500 border-b border-gray-800">
                                <tr>
                                    <th class="py-2 pr-4">{{ __('Gara') }}</th>
                                    <th class="py-2 pr-4">{{ __('Team') }}</th>
                                    <th class="py-2 pr-4">{{ __('Griglia') }}</th>
                                    <th class="py-2 pr-4">{{ __('Posizione') }}</th>
                                    <th class="py-2 pr-4">{{ __('Punti') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800/60">
                                @foreach ($results as $result)
                                    <tr>
                                        <td class="py-2 pr-4">
                                            <a href="{{ route('races.show', $result->race) }}" wire:navigate class="text-gray-200 hover:text-f1red">
                                                R{{ $result->race->round }} &middot; {{ $result->race->name }}
                                            </a>
                                        </td>
                                        <td class="py-2 pr-4 text-gray-400">{{ $result->constructor?->name ?? '—' }}</td>
                                        <td class="py-2 pr-4 text-gray-400">{{ $result->grid ?? '—' }}</td>
                                        <td class="py-2 pr-4 {{ $result->isDnf() ? 'text-red-400' : 'text-gray-200' }}">
                                            {{ $result->position ? 'P'.$result->position : $result->status }}
                                        </td>
                                        <td class="py-2 pr-4 text-gray-400">{{ rtrim(rtrim(number_format($result->real_points, 1), '0'), '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
