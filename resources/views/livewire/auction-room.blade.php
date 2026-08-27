<div wire:poll.10s class="space-y-6">

    @if ($this->currentRound)
        <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <x-icon name="gavel" class="w-5 h-5 text-f1red" /> {{ __('Round asta') }} #{{ $this->currentRound->round_number }}
                </h3>
                <span class="text-sm text-gray-400 flex items-center gap-1.5">
                    <x-icon name="clock" class="w-4 h-4" /> {{ __('Chiude il') }} {{ $this->currentRound->closes_at->format('d/m/Y H:i') }}
                </span>
            </div>

            <div class="mt-4 flex flex-wrap gap-3">
                <span class="inline-flex items-center gap-1.5 text-sm bg-gray-950 border border-gray-800 rounded-md px-3 py-1.5">
                    <x-icon name="coin" class="w-4 h-4 text-f1red" />
                    <span class="text-gray-400">{{ __('Disponibili') }}:</span>
                    <span class="font-semibold text-white">{{ $this->availableBudget }}</span>
                    <span class="text-gray-500">/ {{ $this->member?->budget_remaining }}</span>
                </span>
                @if ($this->committedTotal > 0)
                    <span class="inline-flex items-center gap-1.5 text-sm bg-gray-950 border border-gray-800 rounded-md px-3 py-1.5">
                        <x-icon name="gavel" class="w-4 h-4 text-amber-400" />
                        <span class="text-gray-400">{{ __('Impegnati in offerte') }}:</span>
                        <span class="font-semibold text-amber-400">{{ $this->committedTotal }}</span>
                    </span>
                @endif
                <span class="inline-flex items-center gap-1.5 text-sm bg-gray-950 border border-gray-800 rounded-md px-3 py-1.5">
                    <x-icon name="users" class="w-4 h-4 text-gray-500" />
                    <span class="text-gray-400">{{ __('Piloti') }}:</span>
                    <span class="font-semibold text-white">{{ $this->driverSlotsFilled }}/{{ $league->driver_slots }}</span>
                </span>
                <span class="inline-flex items-center gap-1.5 text-sm bg-gray-950 border border-gray-800 rounded-md px-3 py-1.5">
                    <x-icon name="flag" class="w-4 h-4 text-gray-500" />
                    <span class="text-gray-400">{{ __('Team') }}:</span>
                    <span class="font-semibold text-white">{{ $this->constructorSlotsFilled }}/{{ $league->constructor_slots }}</span>
                </span>
            </div>

            @if ($error)
                <div class="mt-4 p-3 bg-red-900/30 border border-red-800 text-red-300 rounded-lg text-sm">{{ $error }}</div>
            @endif
        </div>

        <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
            <div class="relative mb-6">
                <x-icon name="link" class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Cerca pilota o team...') }}"
                       class="block w-full rounded-md border-gray-700 bg-gray-950 text-gray-200 placeholder-gray-500 focus:border-f1red focus:ring-f1red pl-9" />
            </div>

            <h4 class="font-semibold text-gray-200 mb-3 flex items-center gap-2">
                <x-icon name="users" class="w-4 h-4 text-gray-500" /> {{ __('Piloti') }}
            </h4>
            <div class="space-y-2 mb-8">
                @foreach ($this->availableDrivers as $driver)
                    @php
                        $key = "driver:{$driver->id}";
                        $leading = $this->leadingBids->get($key);
                        $isLeadingMe = $leading && $leading->user_id === auth()->id();
                    @endphp
                    <div class="flex items-center justify-between border border-gray-800 bg-gray-950 rounded-md p-3 gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('drivers.show', $driver) }}" target="_blank" rel="noopener" class="text-gray-200 hover:text-f1red">{{ $driver->full_name }}</a>
                            @if ($leading)
                                <p class="text-xs mt-0.5 flex items-center gap-1 {{ $isLeadingMe ? 'text-green-400' : 'text-amber-400' }}">
                                    <x-icon :name="$isLeadingMe ? 'check-circle' : 'gavel'" class="w-3.5 h-3.5" />
                                    @if ($isLeadingMe)
                                        {{ __('Stai vincendo con') }} {{ $leading->amount }} {{ __('crediti') }}
                                    @else
                                        {{ __('In testa') }} {{ $leading->user->name }} &middot; {{ $leading->amount }} {{ __('crediti') }}
                                    @endif
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if ($isLeadingMe)
                                <button type="button" wire:click="removeBid('driver', {{ $driver->id }})" class="text-xs text-red-400 hover:underline whitespace-nowrap">
                                    {{ __('Rimuovi') }}
                                </button>
                            @else
                                <input type="number" min="{{ $leading ? $leading->amount + 1 : 1 }}" wire:model="bidAmounts.driver:{{ $driver->id }}" placeholder="{{ $leading ? $leading->amount + 1 : 0 }}" class="w-20 rounded-md border-gray-700 bg-gray-900 text-gray-200 focus:border-f1red focus:ring-f1red" />
                                <button type="button" wire:click="placeBid('driver', {{ $driver->id }})" class="text-xs font-semibold text-f1red hover:text-f1red-dark whitespace-nowrap">
                                    {{ $leading ? __('Rilancia') : __('Offri') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <h4 class="font-semibold text-gray-200 mb-3 flex items-center gap-2">
                <x-icon name="flag" class="w-4 h-4 text-gray-500" /> {{ __('Team costruttori') }}
            </h4>
            <div class="space-y-2">
                @foreach ($this->availableConstructors as $constructor)
                    @php
                        $key = "constructor:{$constructor->id}";
                        $leading = $this->leadingBids->get($key);
                        $isLeadingMe = $leading && $leading->user_id === auth()->id();
                    @endphp
                    <div class="flex items-center justify-between border border-gray-800 bg-gray-950 rounded-md p-3 gap-3">
                        <div class="min-w-0">
                            <span class="text-gray-200">{{ $constructor->name }}</span>
                            @if ($leading)
                                <p class="text-xs mt-0.5 flex items-center gap-1 {{ $isLeadingMe ? 'text-green-400' : 'text-amber-400' }}">
                                    <x-icon :name="$isLeadingMe ? 'check-circle' : 'gavel'" class="w-3.5 h-3.5" />
                                    @if ($isLeadingMe)
                                        {{ __('Stai vincendo con') }} {{ $leading->amount }} {{ __('crediti') }}
                                    @else
                                        {{ __('In testa') }} {{ $leading->user->name }} &middot; {{ $leading->amount }} {{ __('crediti') }}
                                    @endif
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if ($isLeadingMe)
                                <button type="button" wire:click="removeBid('constructor', {{ $constructor->id }})" class="text-xs text-red-400 hover:underline whitespace-nowrap">
                                    {{ __('Rimuovi') }}
                                </button>
                            @else
                                <input type="number" min="{{ $leading ? $leading->amount + 1 : 1 }}" wire:model="bidAmounts.constructor:{{ $constructor->id }}" placeholder="{{ $leading ? $leading->amount + 1 : 0 }}" class="w-20 rounded-md border-gray-700 bg-gray-900 text-gray-200 focus:border-f1red focus:ring-f1red" />
                                <button type="button" wire:click="placeBid('constructor', {{ $constructor->id }})" class="text-xs font-semibold text-f1red hover:text-f1red-dark whitespace-nowrap">
                                    {{ $leading ? __('Rilancia') : __('Offri') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="rounded-xl border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-8 text-center text-gray-400">
            <x-icon name="clock" class="w-8 h-8 mx-auto text-gray-600 mb-2" />
            {{ __('Nessun round attivo al momento.') }}
        </div>
    @endif

</div>
