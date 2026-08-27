<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <a href="{{ route('news') }}" wire:navigate class="text-sm text-gray-400 hover:text-f1red flex items-center gap-1 mb-2">
                    <x-icon name="chevron-right" class="w-3.5 h-3.5 rotate-180" /> {{ __('Notizie') }}
                </a>
                <h2 class="font-semibold text-xl text-white leading-tight flex items-center gap-2">
                    <x-icon name="flag" class="w-5 h-5 text-f1red" /> R{{ $race->round }} &middot; {{ $race->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $race->circuit }}{{ $race->country ? ', ' . $race->country : '' }}
                    &middot; {{ $race->starts_at->translatedFormat('d M Y') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <x-icon name="flag" class="w-5 h-5 text-f1red" /> {{ __('Circuito') }}
                </h3>

                <div class="grid gap-6 {{ $race->latitude && $race->longitude ? 'sm:grid-cols-2' : '' }}">
                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center gap-2">
                            <dt class="text-gray-500 w-32 shrink-0">{{ __('Circuito') }}</dt>
                            <dd class="text-gray-200">{{ $race->circuit }}</dd>
                        </div>
                        @if ($race->locality || $race->country)
                            <div class="flex items-center gap-2">
                                <dt class="text-gray-500 w-32 shrink-0">{{ __('Località') }}</dt>
                                <dd class="text-gray-200">{{ implode(', ', array_filter([$race->locality, $race->country])) }}</dd>
                            </div>
                        @endif
                        <div class="flex items-center gap-2">
                            <dt class="text-gray-500 w-32 shrink-0">{{ __('Data') }}</dt>
                            <dd class="text-gray-200">{{ $race->starts_at->translatedFormat('d M Y, H:i') }}</dd>
                        </div>
                        @if ($race->wikipedia_url || $race->circuit_wikipedia_url)
                            <div class="flex items-center gap-2">
                                <dt class="text-gray-500 w-32 shrink-0">{{ __('Approfondisci') }}</dt>
                                <dd class="flex items-center gap-3">
                                    @if ($race->wikipedia_url)
                                        <a href="{{ $race->wikipedia_url }}" target="_blank" rel="noopener" class="text-f1red hover:underline">{{ __('Gara') }}</a>
                                    @endif
                                    @if ($race->circuit_wikipedia_url)
                                        <a href="{{ $race->circuit_wikipedia_url }}" target="_blank" rel="noopener" class="text-f1red hover:underline">{{ __('Circuito') }}</a>
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>

                    @if ($race->latitude && $race->longitude)
                        @php
                            $lat = $race->latitude;
                            $lng = $race->longitude;
                            $bbox = ($lng - 0.045) . ',' . ($lat - 0.025) . ',' . ($lng + 0.045) . ',' . ($lat + 0.025);
                        @endphp
                        <div class="relative rounded-lg overflow-hidden border border-gray-800 ring-1 ring-white/5 shadow-lg shadow-black/40 h-56 sm:h-auto group">
                            <iframe
                                class="w-full h-full min-h-[14rem] pointer-events-none [filter:invert(93%)_hue-rotate(180deg)_brightness(0.95)_contrast(88%)]"
                                loading="lazy"
                                src="https://www.openstreetmap.org/export/embed.html?bbox={{ $bbox }}&marker={{ $lat }},{{ $lng }}&layer=mapnik"
                                title="{{ __('Posizione del circuito') }}">
                            </iframe>

                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent px-3 pt-6 pb-2.5 flex items-center justify-between gap-2">
                                <span class="text-xs text-gray-200 font-medium truncate flex items-center gap-1.5">
                                    <x-icon name="flag" class="w-3.5 h-3.5 text-f1red shrink-0" />
                                    {{ implode(', ', array_filter([$race->locality, $race->country])) ?: $race->circuit }}
                                </span>
                                <a href="https://www.openstreetmap.org/?mlat={{ $lat }}&mlon={{ $lng }}#map=15/{{ $lat }}/{{ $lng }}"
                                   target="_blank" rel="noopener"
                                   class="text-xs font-semibold text-f1red hover:text-f1red-dark whitespace-nowrap shrink-0">
                                    {{ __('Apri mappa') }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($race->status !== 'completed')
                <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-10 text-center text-gray-400">
                    <x-icon name="clock" class="w-8 h-8 mx-auto text-gray-600 mb-2" />
                    {{ __('Risultati non ancora disponibili per questa gara.') }}
                </div>
            @else
                <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <x-icon name="trophy" class="w-5 h-5 text-f1red" /> {{ __('Classifica gara') }}
                    </h3>

                    <p class="text-xs text-gray-500 mb-3 flex items-center gap-1.5">
                        <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                        {{ __('Clicca su un pilota per i tempi sul giro e i pit stop.') }}
                    </p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-gray-500 border-b border-gray-800">
                                <tr>
                                    <th class="py-2 pr-2">{{ __('Pos') }}</th>
                                    <th class="py-2">{{ __('Pilota') }}</th>
                                    <th class="py-2">{{ __('Team') }}</th>
                                    <th class="py-2 text-right">{{ __('Griglia') }}</th>
                                    <th class="py-2 text-right">{{ __('Giri') }}</th>
                                    <th class="py-2 text-right">{{ __('Tempo/Gap') }}</th>
                                    <th class="py-2 text-right">{{ __('Giro veloce') }}</th>
                                    <th class="py-2 text-right">{{ __('Punti') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                @foreach ($results as $r)
                                    @php
                                        $driverLaps = $lapsByDriver->get($r->driver_id, collect());
                                        $driverPitStops = $pitStopsByDriver->get($r->driver_id, collect());
                                        $hasDetail = $driverLaps->isNotEmpty() || $driverPitStops->isNotEmpty();
                                    @endphp
                                    <tr @if ($hasDetail) onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.detail-chevron')?.classList.toggle('rotate-90')" @endif
                                        class="{{ $r->position ? '' : 'opacity-60' }} {{ $hasDetail ? 'cursor-pointer hover:bg-gray-900/40' : '' }}">
                                        <td class="py-3 pr-2">
                                            @if ($r->position)
                                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold {{ $r->position <= 3 ? 'bg-f1red/10 text-f1red' : 'text-gray-500' }}">
                                                    {{ $r->position }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-500 px-1">{{ __('RIT') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-gray-200 font-medium whitespace-nowrap">
                                            <span class="flex items-center gap-1.5">
                                                @if ($r->driver->flag)
                                                    <span class="shrink-0" title="{{ $r->driver->nationality }}">{{ $r->driver->flag }}</span>
                                                @endif
                                                @if ($r->driver->number)
                                                    <span class="text-[11px] font-mono text-gray-500 w-5 text-right shrink-0">{{ $r->driver->number }}</span>
                                                @endif
                                                <a href="{{ route('drivers.show', $r->driver) }}" wire:navigate onclick="event.stopPropagation()" class="hover:text-f1red">
                                                    {{ $r->driver->full_name }}
                                                </a>
                                                @if ($r->driver->code)
                                                    <span class="text-[8px] font-mono bg-gray-950 border border-gray-800 rounded px-1 py-px text-gray-500 shrink-0 leading-tight">{{ $r->driver->code }}</span>
                                                @endif
                                                @if ($hasDetail)
                                                    <x-icon name="chevron-right" class="detail-chevron w-3 h-3 text-gray-500 transition-transform" />
                                                @endif
                                            </span>
                                        </td>
                                        <td class="py-3 text-gray-400 whitespace-nowrap">{{ $r->constructor->name }}</td>
                                        <td class="py-3 text-right text-gray-500">{{ $r->grid ?? '—' }}</td>
                                        <td class="py-3 text-right text-gray-500">{{ $r->laps ?? '—' }}</td>
                                        <td class="py-3 text-right text-gray-400 whitespace-nowrap">
                                            {{ $r->position ? ($r->race_time ?? '—') : $r->status }}
                                        </td>
                                        <td class="py-3 text-right text-gray-500 whitespace-nowrap">
                                            @if ($r->fastest_lap)
                                                <span class="inline-flex items-center gap-1 text-f1red font-medium">
                                                    <x-icon name="clock" class="w-3.5 h-3.5" /> {{ $r->fastest_lap_time }}
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="py-3 text-right font-semibold text-gray-200">{{ rtrim(rtrim(number_format($r->real_points, 1), '0'), '.') }}</td>
                                    </tr>

                                    @if ($hasDetail)
                                        <tr class="hidden">
                                            <td colspan="8" class="bg-gray-950/60 p-4">
                                                @if ($driverLaps->isNotEmpty())
                                                    <div class="mb-5">
                                                        <h5 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1 flex items-center gap-1.5">
                                                            <x-icon name="clock" class="w-3.5 h-3.5" /> {{ __('Andamento tempi sul giro') }}
                                                        </h5>
                                                        @include('partials.lap-time-chart', ['laps' => $driverLaps, 'pitStops' => $driverPitStops])
                                                    </div>
                                                @endif

                                                <div class="grid gap-6 sm:grid-cols-2">
                                                    <div>
                                                        <h5 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                                                            <x-icon name="clock" class="w-3.5 h-3.5" /> {{ __('Tempi sul giro') }}
                                                        </h5>
                                                        @if ($driverLaps->isEmpty())
                                                            <p class="text-xs text-gray-500">{{ __('Dati sui giri non disponibili.') }}</p>
                                                        @else
                                                            <div class="max-h-64 overflow-y-auto rounded-md border border-gray-800">
                                                                <table class="w-full text-xs">
                                                                    <thead class="text-gray-500 sticky top-0 bg-gray-950">
                                                                        <tr>
                                                                            <th class="py-1.5 px-2 text-left">{{ __('Giro') }}</th>
                                                                            <th class="py-1.5 px-2 text-right">{{ __('Pos') }}</th>
                                                                            <th class="py-1.5 px-2 text-right">{{ __('Tempo') }}</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="divide-y divide-gray-800/60">
                                                                        @foreach ($driverLaps as $lap)
                                                                            <tr>
                                                                                <td class="py-1 px-2 text-gray-400">{{ $lap->lap }}</td>
                                                                                <td class="py-1 px-2 text-right text-gray-500">{{ $lap->position ?? '—' }}</td>
                                                                                <td class="py-1 px-2 text-right text-gray-300">{{ $lap->time ?? '—' }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div>
                                                        <h5 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                                                            <x-icon name="gavel" class="w-3.5 h-3.5" /> {{ __('Pit stop') }}
                                                        </h5>
                                                        @if ($driverPitStops->isEmpty())
                                                            <p class="text-xs text-gray-500">{{ __('Nessun pit stop registrato.') }}</p>
                                                        @else
                                                            <ul class="space-y-1.5">
                                                                @foreach ($driverPitStops as $stop)
                                                                    <li class="flex items-center justify-between text-xs bg-gray-900/60 rounded-md px-2.5 py-1.5">
                                                                        <span class="text-gray-400">{{ __('Stop :n · giro :lap', ['n' => $stop->stop, 'lap' => $stop->lap]) }}</span>
                                                                        <span class="text-gray-200 font-medium">{{ $stop->duration ?? '—' }}</span>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <x-icon name="flag" class="w-5 h-5 text-f1red" /> {{ __('Punti costruttori in gara') }}
                    </h3>

                    <ul class="space-y-2">
                        @foreach ($constructorStandings as $i => $c)
                            <li class="flex items-center justify-between text-sm py-1.5 border-b border-gray-800 last:border-0">
                                <span class="flex items-center gap-2 text-gray-200">
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full text-xs {{ $i === 0 ? 'bg-f1red/10 text-f1red' : 'text-gray-500' }}">{{ $i + 1 }}</span>
                                    {{ $c['constructor']->name }}
                                    <span class="text-gray-500 text-xs">
                                        ({{ collect($c['drivers'])->map(fn ($d) => ($d['position'] ? 'P'.$d['position'] : __('RIT')).' '.$d['name'])->implode(', ') }})
                                    </span>
                                </span>
                                <span class="font-semibold text-f1red">{{ rtrim(rtrim(number_format($c['points'], 1), '0'), '.') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
