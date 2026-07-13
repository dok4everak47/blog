@props(['items' => []])

<nav aria-label="面包屑导航" class="mb-6">
    <ol class="flex items-center flex-wrap gap-1.5 text-sm text-text-muted">
        <li>
            <a href="{{ route('home') }}" class="hover:text-primary transition">首页</a>
        </li>
        @foreach ($items as $item)
            <li class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-border-strong shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                @if (isset($item['url']) && $loop->last === false)
                    <a href="{{ $item['url'] }}" class="hover:text-primary transition">{{ $item['label'] }}</a>
                @else
                    <span class="text-text font-medium">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
