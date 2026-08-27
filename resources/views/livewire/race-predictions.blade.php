<div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6" x-data="{ open: false }">
    <button type="button" @click="open = !open" class="w-full flex items-center justify-between gap-2 text-left">
        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
            <x-icon name="dice" class="w-5 h-5 text-f1red" /> {{ $this->locked ? __('Pronostici') : __('Pronostici :race', ['race' => $this->race->name]) }}
        </h3>
        <x-icon name="chevron-right" class="w-5 h-5 text-gray-500 transition-transform shrink-0" x-bind:class="open ? 'rotate-90' : ''" />
    </button>

    <div x-show="open" x-cloak class="mt-4">
        @if ($this->locked)
            <p class="text-sm text-gray-400">
                {{ __('Nessuna gara in programma su cui pronosticare al momento.') }}
            </p>
        @else
            <p class="text-sm text-gray-500 mb-5">
                {{ __('In palio :points punti per ogni pronostico corretto. Puoi scegliere qualsiasi piloti o team, anche se non li possiedi. Le scommesse si chiudono al via della gara e saranno visibili a tutti i membri della lega solo a fine gara.', ['points' => $league->predictionPoints('pole')]) }}
            </p>

            @if ($error)
                <div class="mb-4 p-3 bg-red-900/30 border border-red-800 text-red-300 rounded-lg text-sm">{{ $error }}</div>
            @endif

            <div class="grid gap-6 lg:grid-cols-2">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-gray-200 flex items-center gap-2">
                            <x-icon name="flag" class="w-4 h-4 text-gray-500" /> {{ __('Pole position') }}
                        </h4>
                        <span class="text-xs text-gray-500">+{{ $league->predictionPoints('pole') }} {{ __('punti') }}</span>
                    </div>

                    <div class="relative mb-3">
                        <input type="text" wire:model.live.debounce.300ms="driverSearch" placeholder="{{ __('Cerca pilota...') }}"
                               class="block w-full rounded-md border-gray-700 bg-gray-950 text-gray-200 placeholder-gray-500 focus:border-f1red focus:ring-f1red text-sm" />
                    </div>

                    <div class="space-y-1.5 max-h-64 overflow-y-auto pr-1">
                        @foreach ($this->availableDrivers as $driver)
                            @php $isPicked = $this->myPredictions->get('pole')?->pickable_id === $driver->id; @endphp
                            <div class="flex items-center justify-between border rounded-md px-3 py-2 text-sm {{ $isPicked ? 'border-f1red bg-f1red/10' : 'border-gray-800 bg-gray-950' }}">
                                <a href="{{ route('drivers.show', $driver) }}" target="_blank" rel="noopener" class="text-gray-200 hover:text-f1red truncate">{{ $driver->full_name }}</a>
                                @if ($isPicked)
                                    <button type="button" wire:click="removePrediction('pole')" class="text-xs text-red-400 hover:underline shrink-0">{{ __('Rimuovi') }}</button>
                                @else
                                    <button type="button" wire:click="predict('pole', 'driver', {{ $driver->id }})" class="text-xs font-semibold text-f1red hover:text-f1red-dark shrink-0">{{ __('Scegli') }}</button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-gray-200 flex items-center gap-2">
                            <x-icon name="x-circle" class="w-4 h-4 text-gray-500" /> {{ __('Primo ritiro (DNF)') }}
                        </h4>
                        <span class="text-xs text-gray-500">+{{ $league->predictionPoints('dnf') }} {{ __('punti') }}</span>
                    </div>

                    <div class="relative mb-3">
                        <input type="text" wire:model.live.debounce.300ms="driverSearch" placeholder="{{ __('Cerca pilota...') }}"
                               class="block w-full rounded-md border-gray-700 bg-gray-950 text-gray-200 placeholder-gray-500 focus:border-f1red focus:ring-f1red text-sm" />
                    </div>

                    <div class="space-y-1.5 max-h-64 overflow-y-auto pr-1">
                        @foreach ($this->availableDrivers as $driver)
                            @php $isPicked = $this->myPredictions->get('dnf')?->pickable_id === $driver->id; @endphp
                            <div class="flex items-center justify-between border rounded-md px-3 py-2 text-sm {{ $isPicked ? 'border-f1red bg-f1red/10' : 'border-gray-800 bg-gray-950' }}">
                                <a href="{{ route('drivers.show', $driver) }}" target="_blank" rel="noopener" class="text-gray-200 hover:text-f1red truncate">{{ $driver->full_name }}</a>
                                @if ($isPicked)
                                    <button type="button" wire:click="removePrediction('dnf')" class="text-xs text-red-400 hover:underline shrink-0">{{ __('Rimuovi') }}</button>
                                @else
                                    <button type="button" wire:click="predict('dnf', 'driver', {{ $driver->id }})" class="text-xs font-semibold text-f1red hover:text-f1red-dark shrink-0">{{ __('Scegli') }}</button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-gray-200 flex items-center gap-2">
                            <x-icon name="clock" class="w-4 h-4 text-gray-500" /> {{ __('Pit stop più veloce') }}
                        </h4>
                        <span class="text-xs text-gray-500">+{{ $league->predictionPoints('fastest_pit_stop') }} {{ __('punti') }}</span>
                    </div>

                    <div class="relative mb-3">
                        <input type="text" wire:model.live.debounce.300ms="constructorSearch" placeholder="{{ __('Cerca team...') }}"
                               class="block w-full rounded-md border-gray-700 bg-gray-950 text-gray-200 placeholder-gray-500 focus:border-f1red focus:ring-f1red text-sm" />
                    </div>

                    <div class="grid gap-1.5 sm:grid-cols-2 max-h-64 overflow-y-auto pr-1">
                        @foreach ($this->availableConstructors as $constructor)
                            @php $isPicked = $this->myPredictions->get('fastest_pit_stop')?->pickable_id === $constructor->id; @endphp
                            <div class="flex items-center justify-between border rounded-md px-3 py-2 text-sm {{ $isPicked ? 'border-f1red bg-f1red/10' : 'border-gray-800 bg-gray-950' }}">
                                <span class="text-gray-200 truncate">{{ $constructor->name }}</span>
                                @if ($isPicked)
                                    <button type="button" wire:click="removePrediction('fastest_pit_stop')" class="text-xs text-red-400 hover:underline shrink-0">{{ __('Rimuovi') }}</button>
                                @else
                                    <button type="button" wire:click="predict('fastest_pit_stop', 'constructor', {{ $constructor->id }})" class="text-xs font-semibold text-f1red hover:text-f1red-dark shrink-0">{{ __('Scegli') }}</button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
