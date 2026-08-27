@if (count($breakdowns))
    <div class="mt-8" x-data="{ sectionOpen: false }">
        <button type="button" @click="sectionOpen = !sectionOpen" class="w-full flex items-center justify-between gap-2 text-left mb-3">
            <h4 class="text-sm font-semibold text-gray-300 flex items-center gap-2">
                <x-icon name="flag" class="w-4 h-4 text-f1red" /> {{ __('Punti gara per gara') }}
            </h4>
            <x-icon name="chevron-right" class="w-4 h-4 text-gray-500 transition-transform shrink-0" x-bind:class="sectionOpen ? 'rotate-90' : ''" />
        </button>

        <div x-show="sectionOpen" x-cloak class="space-y-2">
            @foreach ($breakdowns as $i => $entry)
                <div class="rounded-lg border border-gray-800 bg-gray-950" x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                            class="w-full flex items-center justify-between gap-3 px-4 py-3 text-left">
                        <span class="text-sm text-gray-200 font-medium truncate">
                            R{{ $entry['race']['round'] }} &middot; {{ $entry['race']['name'] }}
                        </span>
                        <span class="flex items-center gap-3 shrink-0">
                            @foreach ($entry['members'] as $m)
                                <span class="hidden sm:inline-flex items-center gap-1 text-xs font-semibold"
                                      style="color: {{ $m['total'] >= 0 ? $m['color'] : '#e66767' }}">
                                    <span class="inline-block w-2 h-2 rounded-full" style="background-color: {{ $m['color'] }}"></span>
                                    {{ $m['total'] >= 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($m['total'], 1), '0'), '.') }}
                                </span>
                            @endforeach
                            <x-icon name="chevron-right" class="w-4 h-4 text-gray-500 transition-transform" x-bind:class="open ? 'rotate-90' : ''" />
                        </span>
                    </button>

                    <div x-show="open" x-cloak class="px-4 pb-4 grid gap-4 sm:grid-cols-2">
                        @foreach ($entry['members'] as $m)
                            <div class="rounded-md border border-gray-800 bg-gray-900/60 p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="flex items-center gap-1.5 text-sm font-medium text-gray-200">
                                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background-color: {{ $m['color'] }}"></span>
                                        {{ $m['name'] }}
                                    </span>
                                    <span class="text-sm font-semibold" style="color: {{ $m['color'] }}">
                                        {{ $m['total'] >= 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($m['total'], 1), '0'), '.') }}
                                    </span>
                                </div>

                                <ul class="space-y-1 text-xs">
                                    @foreach ($m['drivers'] as $d)
                                        <li class="flex items-center justify-between gap-2">
                                            <span class="text-gray-300 truncate">{{ $d['name'] }}</span>
                                            <span class="text-gray-500 truncate flex-1 text-right px-2">{{ $d['detail'] }}</span>
                                            <span class="font-semibold shrink-0 {{ $d['points'] < 0 ? 'text-red-400' : 'text-green-400' }}">
                                                {{ $d['points'] >= 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($d['points'], 1), '0'), '.') }}
                                            </span>
                                        </li>
                                    @endforeach

                                    @foreach ($m['constructors'] as $c)
                                        <li class="flex items-center justify-between gap-2">
                                            <span class="text-gray-300 truncate flex items-center gap-1">
                                                <x-icon name="flag" class="w-3 h-3 text-gray-600" /> {{ $c['name'] }}
                                            </span>
                                            <span class="text-gray-500 truncate flex-1 text-right px-2">{{ $c['detail'] }}</span>
                                            <span class="font-semibold shrink-0 {{ $c['points'] < 0 ? 'text-red-400' : 'text-green-400' }}">
                                                {{ $c['points'] >= 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($c['points'], 1), '0'), '.') }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>

                                @if (count($m['predictions']))
                                    <ul class="mt-2 pt-2 border-t border-gray-800 space-y-1 text-xs">
                                        @foreach ($m['predictions'] as $p)
                                            <li class="flex items-center justify-between gap-2">
                                                <span class="text-gray-400 truncate flex items-center gap-1">
                                                    <x-icon :name="$p['correct'] ? 'check-circle' : 'x-circle'" class="w-3 h-3 {{ $p['correct'] ? 'text-green-500' : 'text-gray-600' }}" />
                                                    {{ $p['label'] }}: {{ $p['name'] }}
                                                </span>
                                                <span class="font-semibold shrink-0 {{ $p['points'] > 0 ? 'text-green-400' : 'text-gray-500' }}">
                                                    {{ $p['points'] > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($p['points'], 1), '0'), '.') }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
