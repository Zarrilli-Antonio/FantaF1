<div {{ $this->hasLiveRace ? 'wire:poll.60s' : '' }} class="space-y-4">

    @if (empty($this->feed))
        <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-10 text-center text-gray-400">
            <x-icon name="flag" class="w-8 h-8 mx-auto text-gray-600 mb-2" />
            {{ __('Nessuna notizia disponibile ancora.') }}
        </div>
    @endif

    @foreach ($this->feed as $entry)
        @php $race = $entry['race']; $recap = $entry['recap']; @endphp

        <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
            <div class="flex items-center justify-between gap-3 mb-3 flex-wrap">
                <h3 class="text-base font-semibold text-white">
                    R{{ $race->round }} &middot; {{ $race->name }}
                </h3>

                @if ($entry['state'] === 'live')
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-400 bg-red-950/40 border border-red-900 rounded-full px-2.5 py-1">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                        </span>
                        {{ __('IN CORSO') }}
                    </span>
                @else
                    <span class="text-xs text-gray-500">{{ $race->starts_at->translatedFormat('d M Y') }}</span>
                @endif
            </div>

            @if ($entry['state'] === 'live')
                <p class="text-sm text-gray-400">
                    {{ __('Gara iniziata') }} {{ $race->starts_at->diffForHumans() }}.
                    {{ __('I risultati e il recap appariranno qui automaticamente non appena disponibili.') }}
                </p>
            @else
                <p class="text-sm text-gray-300 mb-4">{{ $recap['summary'] }}</p>

                <div class="flex flex-wrap gap-2 text-xs">
                    @if ($recap['winner'])
                        <span class="inline-flex items-center gap-1 bg-gray-950 border border-gray-800 rounded-full px-2.5 py-1 text-gray-300">
                            <x-icon name="trophy" class="w-3.5 h-3.5 text-f1red" /> {{ $recap['winner'] }}
                        </span>
                    @endif
                    @if ($recap['pole'])
                        <span class="inline-flex items-center gap-1 bg-gray-950 border border-gray-800 rounded-full px-2.5 py-1 text-gray-400">
                            <x-icon name="flag" class="w-3.5 h-3.5" /> {{ __('Pole') }}: {{ $recap['pole'] }}
                        </span>
                    @endif
                    @if ($recap['fastestLap'])
                        <span class="inline-flex items-center gap-1 bg-gray-950 border border-gray-800 rounded-full px-2.5 py-1 text-gray-400">
                            <x-icon name="clock" class="w-3.5 h-3.5" /> {{ __('Giro veloce') }}: {{ $recap['fastestLap'] }}
                        </span>
                    @endif
                    @if ($recap['fastestPitStop'])
                        <span class="inline-flex items-center gap-1 bg-gray-950 border border-gray-800 rounded-full px-2.5 py-1 text-gray-400">
                            <x-icon name="clock" class="w-3.5 h-3.5" />
                            {{ __('Pit stop più veloce') }}:
                            {{ $recap['fastestPitStop']['driver'] }}
                            @if ($recap['fastestPitStop']['constructor'])
                                ({{ $recap['fastestPitStop']['constructor'] }})
                            @endif
                            &middot; {{ $recap['fastestPitStop']['duration'] }}s
                        </span>
                    @endif
                    @if (count($recap['retirements']))
                        <span class="inline-flex items-center gap-1 bg-gray-950 border border-gray-800 rounded-full px-2.5 py-1 text-gray-500">
                            {{ count($recap['retirements']) }} {{ __('ritiri') }}
                        </span>
                    @endif
                </div>

                <a href="{{ route('races.show', $race) }}" wire:navigate
                   class="mt-4 inline-flex items-center gap-1 text-sm text-f1red hover:underline">
                    {{ __('Statistiche complete piloti e team') }} <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                </a>
            @endif
        </div>
    @endforeach

</div>
