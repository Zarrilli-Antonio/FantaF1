<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0b0b0c">

        <title>Fanta F1 &mdash; {{ __('il fantacalcio della Formula 1') }}</title>

        @include('partials.favicon')
        <link rel="manifest" href="/build/manifest.webmanifest">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans bg-black text-gray-300">
        <div class="bg-[radial-gradient(ellipse_120%_50%_at_50%_-10%,rgba(225,6,0,0.16),transparent),linear-gradient(180deg,#000000,#0b0b0d_30%,#000000_70%,#000000)]">

        <livewire:layout.navigation />

        <main>
            <!-- Hero -->
            <section class="max-w-6xl mx-auto px-6 py-20 text-center">
                <p class="text-f1red font-semibold tracking-wide uppercase text-sm mb-4">{{ __('Fantasy Formula 1') }}</p>
                <h1 class="text-4xl sm:text-5xl font-extrabold text-white leading-tight">
                    {{ __('Il fantacalcio della Formula 1') }}
                </h1>
                <p class="mt-6 text-lg text-gray-400 max-w-2xl mx-auto">
                    {{ __('Crea la tua lega privata, fai la tua asta a crediti per comporre la rosa di piloti e team, e sfida i tuoi amici gara dopo gara con i punteggi calcolati sui risultati reali del Mondiale.') }}
                </p>
                <div class="mt-10 flex items-center justify-center gap-4">
                    @auth
                        <a href="{{ route('leagues.index') }}" wire:navigate class="px-6 py-3 rounded-md bg-gradient-to-r from-f1red to-f1red-dark text-white font-semibold shadow-lg shadow-f1red/20 hover:brightness-110 transition">
                            {{ __('Vai alle tue leghe') }}
                        </a>
                    @else
                        <a href="{{ route('register') }}" wire:navigate class="px-6 py-3 rounded-md bg-gradient-to-r from-f1red to-f1red-dark text-white font-semibold shadow-lg shadow-f1red/20 hover:brightness-110 transition">
                            {{ __('Inizia gratis') }}
                        </a>
                        <a href="{{ route('login') }}" wire:navigate class="px-6 py-3 rounded-md border border-gray-700 text-gray-200 font-semibold hover:border-gray-500 transition">
                            {{ __('Ho già un account') }}
                        </a>
                    @endauth
                </div>
            </section>

            <!-- Come funziona -->
            <section class="max-w-6xl mx-auto px-6 py-16 border-t border-gray-800">
                <h2 class="text-2xl font-bold text-white text-center mb-12">{{ __('Come funziona') }}</h2>

                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="flex flex-col gap-3">
                        <div class="h-10 w-10 rounded-full bg-f1red/10 text-f1red font-bold flex items-center justify-center">1</div>
                        <h3 class="font-semibold text-white">{{ __('Crea o unisciti a una lega') }}</h3>
                        <p class="text-sm text-gray-400">
                            {{ __('Apri una lega privata e invita i tuoi amici con un codice, oppure unisciti a una lega esistente.') }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <div class="h-10 w-10 rounded-full bg-f1red/10 text-f1red font-bold flex items-center justify-center">2</div>
                        <h3 class="font-semibold text-white">{{ __("Partecipa all'asta") }}</h3>
                        <p class="text-sm text-gray-400">
                            {{ __('Ogni utente ha un budget di crediti. Fai le tue offerte sui piloti e sui team che vuoi: vince chi offre di più, un pilota per lega può andare a una sola persona.') }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <div class="h-10 w-10 rounded-full bg-f1red/10 text-f1red font-bold flex items-center justify-center">3</div>
                        <h3 class="font-semibold text-white">{{ __('Blocca la rosa') }}</h3>
                        <p class="text-sm text-gray-400">
                            {{ __("Finita l'asta la rosa resta fissa per tutta la stagione: niente mercato, come nel fantacalcio classico.") }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <div class="h-10 w-10 rounded-full bg-f1red/10 text-f1red font-bold flex items-center justify-center">4</div>
                        <h3 class="font-semibold text-white">{{ __('Segui la classifica') }}</h3>
                        <p class="text-sm text-gray-400">
                            {{ __('Dopo ogni Gran Premio i punteggi si aggiornano da soli in base ai risultati reali, con bonus e malus.') }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Regole del punteggio -->
            <section class="max-w-6xl mx-auto px-6 py-16 border-t border-gray-800">
                <h2 class="text-2xl font-bold text-white text-center mb-12">{{ __('Come si calcolano i punti') }}</h2>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                        <h3 class="font-semibold text-white mb-2">{{ __('Punti di posizione') }}</h3>
                        <p class="text-sm text-gray-400">
                            {{ __('Ogni pilota in rosa segna punti in base alla posizione di arrivo in gara, dal 1° al 10°.') }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                        <h3 class="font-semibold text-white mb-2">{{ __('Bonus') }}</h3>
                        <p class="text-sm text-gray-400">
                            {{ __('Punti extra per pole position, giro più veloce e arrivo sul podio.') }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                        <h3 class="font-semibold text-white mb-2">{{ __('Malus DNF') }}</h3>
                        <p class="text-sm text-gray-400">
                            {{ __('Penalità in caso di ritiro senza classificazione ufficiale.') }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                        <h3 class="font-semibold text-white mb-2">{{ __('Punti del team') }}</h3>
                        <p class="text-sm text-gray-400">
                            {{ __('Il costruttore in rosa aggiunge i punti reali conquistati dai suoi due piloti in gara.') }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                        <h3 class="font-semibold text-white mb-2">{{ __('Dati reali') }}</h3>
                        <p class="text-sm text-gray-400">
                            {{ __('Calendario, piloti e risultati vengono importati automaticamente dai dati ufficiali del Mondiale.') }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-gray-800/80 bg-gradient-to-b from-gray-900 to-gray-950/80 p-6">
                        <h3 class="font-semibold text-white mb-2">{{ __('Nessun mercato') }}</h3>
                        <p class="text-sm text-gray-400">
                            {{ __("Rosa fissa per tutta la stagione: la sfida si gioca tutta sull'asta iniziale.") }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- CTA finale -->
            <section class="max-w-6xl mx-auto px-6 py-20 text-center border-t border-gray-800">
                <h2 class="text-2xl font-bold text-white mb-4">{{ __('Pronto a schierare la tua scuderia?') }}</h2>
                <p class="text-gray-400 mb-8">{{ __('Registrati, crea una lega e invita i tuoi amici prima che inizi il Mondiale :year.', ['year' => now()->year]) }}</p>
                @guest
                    <a href="{{ route('register') }}" wire:navigate class="px-6 py-3 rounded-md bg-gradient-to-r from-f1red to-f1red-dark text-white font-semibold shadow-lg shadow-f1red/20 hover:brightness-110 transition">
                        {{ __('Crea il tuo account') }}
                    </a>
                @else
                    <a href="{{ route('leagues.index') }}" wire:navigate class="px-6 py-3 rounded-md bg-gradient-to-r from-f1red to-f1red-dark text-white font-semibold shadow-lg shadow-f1red/20 hover:brightness-110 transition">
                        {{ __('Vai alle tue leghe') }}
                    </a>
                @endguest
            </section>
        </main>

        <footer class="border-t border-gray-800/80 py-8 text-center text-xs text-gray-500">
            {{ __('Fanta F1 · progetto self-hosted, non affiliato alla Formula 1® o alla FIA.') }}
        </footer>
        </div>
    </body>
</html>
