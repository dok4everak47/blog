@props(['active' => false])

<a {{ $attributes->merge(['class' => 'block w-full px-4 py-2 text-start text-sm leading-5 text-text transition duration-150 ease-in-out hover:bg-surface hover:text-primary focus:outline-none focus:bg-surface ' . ($active ? 'bg-surface' : '')]) }}>
    {{ $slot }}
</a>
