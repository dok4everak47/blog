@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-text mb-2']) }}>
    {{ $value ?? $slot }}
</label>
