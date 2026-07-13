@props([
    'icon' => 'empty',
    'title' => '暂无内容',
    'description' => null,
    'actionText' => null,
    'actionUrl' => null,
])

<div class="rounded-2xl border border-dashed border-border p-16 text-center bg-surface-2">
    {{-- 图标 --}}
    <div class="w-16 h-16 rounded-full bg-surface flex items-center justify-center mx-auto mb-4">
        @switch($icon)
            @case('search')
                <svg class="w-8 h-8 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                @break
            @case('article')
                <svg class="w-8 h-8 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                @break
            @default
                <svg class="w-8 h-8 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
        @endswitch
    </div>

    <p class="text-text font-medium mb-1">{{ $title }}</p>
    @if($description)
        <p class="text-sm text-text-muted mb-4">{{ $description }}</p>
    @endif

    @if($actionText && $actionUrl)
        <a href="{{ $actionUrl }}"
           class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-hover transition">
            {{ $actionText }}
        </a>
    @endif
</div>
