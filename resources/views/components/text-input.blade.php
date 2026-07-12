@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'w-full rounded-lg border border-border bg-surface-2 px-4 py-2.5 text-sm text-text placeholder:text-text-muted outline-none transition-all duration-200 hover:border-border-strong focus:border-primary focus:ring-2 focus:ring-primary/15 disabled:bg-surface disabled:text-text-secondary']) !!}>
