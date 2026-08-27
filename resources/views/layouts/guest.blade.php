<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0b0b0c">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @include('partials.favicon')
        <link rel="manifest" href="/build/manifest.webmanifest">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-300 antialiased bg-black">
        <div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-10 sm:pt-0 px-4 bg-[radial-gradient(ellipse_120%_60%_at_50%_-10%,rgba(225,6,0,0.14),transparent),linear-gradient(180deg,#000000,#0b0b0d_40%,#000000)]">
            <div class="absolute top-4 right-4">
                <x-language-switcher />
            </div>

            <a href="/" wire:navigate class="flex items-center gap-2 mb-6">
                <x-application-logo class="w-10 h-10 text-f1red" />
                <span class="text-2xl font-bold text-white">Fanta F1</span>
            </a>

            <div class="w-full sm:max-w-md px-6 py-8 bg-gradient-to-b from-gray-900 to-gray-950/80 border border-gray-800/80 shadow-xl shadow-black/40 overflow-hidden sm:rounded-xl">
                {{ $slot }}
            </div>

            <p class="mt-6 text-xs text-gray-500">{{ __('Il fantacalcio della Formula 1 · self-hosted') }}</p>
        </div>
    </body>
</html>
