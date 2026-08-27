@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-700 bg-gray-950 text-gray-200 placeholder-gray-500 focus:border-f1red focus:ring-f1red rounded-md shadow-sm']) }}>
