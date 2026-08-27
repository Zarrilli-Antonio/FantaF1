<div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
        <x-icon name="trophy" class="w-5 h-5 text-f1red" /> {{ __('Classifica') }}
    </h3>

    @if ($this->standings->isEmpty())
        <div class="text-center py-10 text-gray-400">
            <x-icon name="flag" class="w-8 h-8 mx-auto text-gray-600 mb-2" />
            {{ __('Nessun punteggio calcolato ancora. Torna dopo il prossimo Gran Premio.') }}
        </div>
    @else
        @include('livewire.partials.score-chart', ['chart' => $this->chartSeries])

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-gray-500 border-b border-gray-800">
                    <tr>
                        <th class="py-2 pr-2">#</th>
                        <th class="py-2">{{ __('Utente') }}</th>
                        <th class="py-2">{{ __('Gare') }}</th>
                        <th class="py-2 text-right">{{ __('Punti') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach ($this->standings as $i => $row)
                        <tr>
                            <td class="py-3 pr-2">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold {{ $i === 0 ? 'bg-f1red/10 text-f1red' : 'text-gray-500' }}">
                                    {{ $i + 1 }}
                                </span>
                            </td>
                            <td class="py-3 text-gray-200 flex items-center gap-2">
                                <span class="h-6 w-6 rounded-full flex items-center justify-center text-xs font-semibold text-white" style="background-color: {{ $row->color }}">
                                    {{ strtoupper(substr($row->user->name, 0, 1)) }}
                                </span>
                                {{ $row->user->name }}
                            </td>
                            <td class="py-3 text-gray-500">{{ $row->races_scored }}</td>
                            <td class="py-3 text-right font-semibold" style="color: {{ $row->color }}">{{ number_format($row->total_points, 1) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @include('livewire.partials.race-breakdowns', ['breakdowns' => $this->raceBreakdowns])
    @endif
</div>
