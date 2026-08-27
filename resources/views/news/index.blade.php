<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight flex items-center gap-2">
            <x-icon name="flag" class="w-5 h-5 text-f1red" /> {{ __('Notizie') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <livewire:race-news />
        </div>
    </div>
</x-app-layout>
