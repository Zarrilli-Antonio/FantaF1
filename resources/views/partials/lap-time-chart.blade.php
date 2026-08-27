@php
    $points = $laps->map(fn ($lap) => ['lap' => $lap->lap, 'seconds' => $lap->seconds, 'time' => $lap->time])
        ->filter(fn ($p) => $p['seconds'] !== null)
        ->values();
    $pitLaps = $pitStops->pluck('lap')->map(fn ($l) => (int) $l)->flip();
    $n = $points->count();
@endphp

@if ($n > 1)
    @php
        $vbW = 640; $vbH = 160;
        $padL = 42; $padR = 12; $padT = 12; $padB = 22;
        $plotW = $vbW - $padL - $padR;
        $plotH = $vbH - $padT - $padB;

        $seconds = $points->pluck('seconds');
        $minV = $seconds->min();
        $maxV = $seconds->max();
        $range = max(0.5, $maxV - $minV);
        // Pad the domain a bit so the fastest/slowest points aren't glued to the edges.
        $domainMin = $minV - $range * 0.1;
        $domainMax = $maxV + $range * 0.1;
        $domain = max(0.1, $domainMax - $domainMin);

        $minLap = $points->first()['lap'];
        $maxLap = $points->last()['lap'];
        $lapSpan = max(1, $maxLap - $minLap);

        $xFor = fn ($lap) => $padL + (($lap - $minLap) / $lapSpan) * $plotW;
        $yFor = fn ($v) => $padT + $plotH - (($v - $domainMin) / $domain) * $plotH;

        $fmt = function (float $s) {
            $m = (int) floor($s / 60);
            $rest = $s - $m * 60;

            return $m > 0 ? sprintf('%d:%06.3f', $m, $rest) : sprintf('%.3f', $rest);
        };

        $fastest = $points->sortBy('seconds')->first();

        $lineForJs = $points->map(fn ($p) => ['lap' => $p['lap'], 'time' => $p['time'], 'pit' => $pitLaps->has($p['lap'])])->all();
        $xPositions = $points->map(fn ($p) => $xFor($p['lap']))->all();

        $ticks = [$domainMin, $domainMin + $domain / 2, $domainMax];
        $labelStep = max(1, (int) ceil($n / 8));
    @endphp

    <div class="mt-3" x-data="{
            hoverIdx: null,
            xPositions: {{ \Illuminate\Support\Js::from($xPositions) }},
            points: {{ \Illuminate\Support\Js::from($lineForJs) }},
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
        <div class="relative" @mousemove="setHover" @mouseleave="hoverIdx = null">
            <svg viewBox="0 0 {{ $vbW }} {{ $vbH }}" class="w-full h-auto block" preserveAspectRatio="none">
                <rect x="0" y="0" width="{{ $vbW }}" height="{{ $vbH }}" fill="#030712" />

                @foreach ($ticks as $t)
                    <line x1="{{ $padL }}" y1="{{ $yFor($t) }}" x2="{{ $vbW - $padR }}" y2="{{ $yFor($t) }}" stroke="#2c2c2a" stroke-width="1" />
                    <text x="{{ $padL - 4 }}" y="{{ $yFor($t) + 3 }}" text-anchor="end" font-size="8" fill="#898781">{{ $fmt($t) }}</text>
                @endforeach

                @foreach ($points as $i => $p)
                    @if ($i % $labelStep === 0 || $i === $n - 1)
                        <text x="{{ $xFor($p['lap']) }}" y="{{ $vbH - 6 }}" text-anchor="middle" font-size="8" fill="#898781">{{ $p['lap'] }}</text>
                    @endif
                @endforeach

                @php $linePoints = $points->map(fn ($p) => $xFor($p['lap']) . ',' . $yFor($p['seconds']))->implode(' '); @endphp
                <polyline points="{{ $linePoints }}" fill="none" stroke="#e10600" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />

                @foreach ($points as $p)
                    @if ($pitLaps->has($p['lap']))
                        <circle cx="{{ $xFor($p['lap']) }}" cy="{{ $yFor($p['seconds']) }}" r="3.5" fill="#c98500" stroke="#030712" stroke-width="1.5" />
                    @endif
                @endforeach

                <circle cx="{{ $xFor($fastest['lap']) }}" cy="{{ $yFor($fastest['seconds']) }}" r="3.5" fill="#199e70" stroke="#030712" stroke-width="1.5" />

                <template x-if="hoverIdx !== null">
                    <line :x1="xPositions[hoverIdx]" :x2="xPositions[hoverIdx]" y1="{{ $padT }}" y2="{{ $vbH - $padB }}" stroke="#52514e" stroke-width="1" stroke-dasharray="3,3" />
                </template>
            </svg>

            <div x-show="hoverIdx !== null" x-cloak
                 class="absolute top-1 -translate-x-1/2 bg-gray-950 border border-gray-700 rounded-md px-2.5 py-1.5 text-xs shadow-xl pointer-events-none z-10 whitespace-nowrap"
                 :style="'left: ' + (xPositions[hoverIdx] / {{ $vbW }} * 100) + '%'">
                <span class="text-gray-300 font-semibold" x-text="'Giro ' + points[hoverIdx].lap"></span>
                <span class="text-gray-400">·</span>
                <span class="text-gray-200" x-text="points[hoverIdx].time"></span>
                <span x-show="points[hoverIdx].pit" x-cloak class="text-amber-400">· {{ __('Pit stop') }}</span>
            </div>
        </div>

        <div class="flex items-center gap-4 mt-1.5 text-[11px] text-gray-500">
            <span class="inline-flex items-center gap-1"><span class="inline-block w-2 h-2 rounded-full bg-[#199e70]"></span> {{ __('Giro più veloce') }} ({{ $fastest['time'] }})</span>
            @if ($pitLaps->count())
                <span class="inline-flex items-center gap-1"><span class="inline-block w-2 h-2 rounded-full bg-[#c98500]"></span> {{ __('Pit stop') }}</span>
            @endif
        </div>
    </div>
@endif
