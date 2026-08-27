@php
    $races = $chart['races'];
    $series = $chart['series'];
    $n = count($races);
@endphp

@if ($n > 1 && count($series))
    @php
        // Viewport & padding (SVG user units).
        $vbW = 720; $vbH = 260;
        $padL = 38; $padR = 16; $padT = 16; $padB = 30;
        $plotW = $vbW - $padL - $padR;
        $plotH = $vbH - $padT - $padB;

        $allValues = collect($series)->flatMap(fn ($s) => $s['values']);
        $maxV = max(0, $allValues->max() ?? 0);
        $minV = min(0, $allValues->min() ?? 0);
        // Round the domain out to a clean step so axis ticks land on nice numbers.
        $range = max(1, $maxV - $minV);
        $step = pow(10, floor(log10($range / 4)));
        $niceMax = ceil($maxV / $step) * $step;
        $niceMin = floor($minV / $step) * $step;
        $domain = max(1, $niceMax - $niceMin);

        $xFor = fn ($i) => $n > 1 ? $padL + ($i / ($n - 1)) * $plotW : $padL;
        $yFor = fn ($v) => $padT + $plotH - (($v - $niceMin) / $domain) * $plotH;

        $xPositions = collect(range(0, $n - 1))->map($xFor)->all();

        // Show at most ~10 x-axis labels to avoid crowding on long seasons.
        $labelStep = max(1, (int) ceil($n / 10));

        // Direct end-labels: sort by y (screen position), then push apart so none overlap.
        $endLabels = collect($series)->map(function ($s) use ($xFor, $yFor, $n) {
            return [
                'name' => $s['name'],
                'color' => $s['color'],
                'total' => $s['total'],
                'x' => $xFor($n - 1),
                'y' => $yFor($s['total']),
            ];
        })->sortBy('y')->values();

        $minGap = 14;
        for ($i = 1; $i < $endLabels->count(); $i++) {
            $prev = $endLabels[$i - 1];
            $cur = $endLabels[$i];
            if ($cur['y'] < $prev['y'] + $minGap) {
                $cur['y'] = $prev['y'] + $minGap;
                $endLabels->put($i, $cur);
            }
        }

        $ticks = [$niceMin, $niceMin + $domain / 2, $niceMax];

        $seriesForJs = collect($series)->map(fn ($s) => [
            'name' => $s['name'],
            'color' => $s['color'],
            'values' => $s['values'],
        ])->all();
        $raceNamesForJs = collect($races)->map(fn ($r) => $r['name'])->all();
    @endphp

    <div class="mb-8" x-data="{
            hoverIdx: null,
            xPositions: {{ \Illuminate\Support\Js::from($xPositions) }},
            races: {{ \Illuminate\Support\Js::from($raceNamesForJs) }},
            series: {{ \Illuminate\Support\Js::from($seriesForJs) }},
            setHover(event) {
                const ratio = event.offsetX / $el.clientWidth;
                const svgX = ratio * {{ $vbW }};
                let best = 0, bestDist = Infinity;
                this.xPositions.forEach((x, i) => {
                    const d = Math.abs(x - svgX);
                    if (d < bestDist) { bestDist = d; best = i; }
                });
                this.hoverIdx = best;
            },
        }" class="relative">

        <div class="flex items-center gap-4 flex-wrap mb-3">
            @foreach ($series as $s)
                <span class="inline-flex items-center gap-1.5 text-xs text-gray-400">
                    <span class="inline-block w-2.5 h-2.5 rounded-full" style="background-color: {{ $s['color'] }}"></span>
                    {{ $s['name'] }}
                </span>
            @endforeach
        </div>

        <div class="relative" @mousemove="setHover" @mouseleave="hoverIdx = null">
            <svg viewBox="0 0 {{ $vbW }} {{ $vbH }}" class="w-full h-auto block" preserveAspectRatio="none">
                <rect x="0" y="0" width="{{ $vbW }}" height="{{ $vbH }}" fill="#030712" />

                {{-- gridlines --}}
                @foreach ($ticks as $t)
                    <line x1="{{ $padL }}" y1="{{ $yFor($t) }}" x2="{{ $vbW - $padR }}" y2="{{ $yFor($t) }}"
                          stroke="#2c2c2a" stroke-width="1" />
                    <text x="{{ $padL - 6 }}" y="{{ $yFor($t) + 3 }}" text-anchor="end" font-size="9" fill="#898781">{{ number_format($t, 0) }}</text>
                @endforeach

                {{-- x-axis labels --}}
                @foreach ($races as $i => $race)
                    @if ($i % $labelStep === 0 || $i === $n - 1)
                        <text x="{{ $xFor($i) }}" y="{{ $vbH - 10 }}" text-anchor="middle" font-size="9" fill="#898781">{{ $race['label'] }}</text>
                    @endif
                @endforeach

                {{-- series lines --}}
                @foreach ($series as $s)
                    @php
                        $points = collect($s['values'])->map(fn ($v, $i) => $xFor($i) . ',' . $yFor($v))->implode(' ');
                    @endphp
                    <polyline points="{{ $points }}" fill="none" stroke="{{ $s['color'] }}" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round" />
                    <circle cx="{{ $xFor($n - 1) }}" cy="{{ $yFor($s['total']) }}" r="4" fill="{{ $s['color'] }}"
                            stroke="#030712" stroke-width="2" />
                @endforeach

                {{-- direct end-labels --}}
                @foreach ($endLabels as $label)
                    <text x="{{ $label['x'] + 8 }}" y="{{ $label['y'] + 3 }}" font-size="10" font-weight="600" fill="{{ $label['color'] }}">
                        {{ number_format($label['total'], 0) }}
                    </text>
                @endforeach

                {{-- hover crosshair --}}
                <template x-if="hoverIdx !== null">
                    <line :x1="xPositions[hoverIdx]" :x2="xPositions[hoverIdx]" y1="{{ $padT }}" y2="{{ $vbH - $padB }}"
                          stroke="#52514e" stroke-width="1" stroke-dasharray="3,3" />
                </template>
            </svg>

            {{-- tooltip --}}
            <div x-show="hoverIdx !== null" x-cloak
                 class="absolute top-1 -translate-x-1/2 bg-gray-950 border border-gray-700 rounded-md px-3 py-2 text-xs shadow-xl pointer-events-none z-10 whitespace-nowrap"
                 :style="'left: ' + (xPositions[hoverIdx] / {{ $vbW }} * 100) + '%'">
                <p class="text-gray-300 font-semibold mb-1" x-text="races[hoverIdx]"></p>
                <template x-for="s in series" :key="s.name">
                    <p class="flex items-center gap-1.5 text-gray-400">
                        <span class="inline-block w-2 h-2 rounded-full" :style="'background-color:' + s.color"></span>
                        <span x-text="s.name"></span>:
                        <span class="font-semibold text-gray-200" x-text="s.values[hoverIdx]"></span>
                    </p>
                </template>
            </div>
        </div>
    </div>
@endif
