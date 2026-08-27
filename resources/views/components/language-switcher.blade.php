@php
    $locales = [
        'it' => ['label' => 'Italiano', 'flag' => '🇮🇹'],
        'en' => ['label' => 'English', 'flag' => '🇬🇧'],
        'de' => ['label' => 'Deutsch', 'flag' => '🇩🇪'],
        'fr' => ['label' => 'Français', 'flag' => '🇫🇷'],
    ];
    $current = $locales[app()->getLocale()] ?? $locales['it'];
@endphp

{{-- Plain <details>/<summary> disclosure: no Alpine/JS dependency, so it works
     on every page regardless of whether Livewire (and its bundled Alpine) is
     present — including pages with no Livewire component at all. --}}
<details class="relative language-switcher">
    <summary class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md border border-gray-800 text-sm text-gray-300 hover:border-gray-700 hover:text-white transition cursor-pointer list-none [&::-webkit-details-marker]:hidden">
        <span>{{ $current['flag'] }}</span>
        <span class="hidden sm:inline">{{ $current['label'] }}</span>
    </summary>

    <div class="absolute right-0 mt-2 w-40 rounded-md bg-gray-900 border border-gray-800 shadow-xl z-50 overflow-hidden">
        @foreach ($locales as $code => $meta)
            <form method="POST" action="{{ route('locale.update', $code) }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left transition {{ $code === app()->getLocale() ? 'bg-f1red/10 text-f1red' : 'text-gray-300 hover:bg-gray-800' }}">
                    <span>{{ $meta['flag'] }}</span> {{ $meta['label'] }}
                </button>
            </form>
        @endforeach
    </div>
</details>
