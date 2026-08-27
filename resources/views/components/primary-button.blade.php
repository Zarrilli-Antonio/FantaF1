<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-f1red to-f1red-dark border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-lg shadow-f1red/20 hover:brightness-110 focus:brightness-110 active:brightness-95 focus:outline-none focus:ring-2 focus:ring-f1red focus:ring-offset-2 focus:ring-offset-gray-900 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
