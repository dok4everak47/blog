@if($relatedNotes->isNotEmpty())
<section class="mt-12 pt-10 border-t border-border">
    <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-5">相关文章</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach($relatedNotes as $related)
            <a href="{{ route('notes.show', $related) }}"
               class="group rounded-xl border border-border bg-surface-2 p-5 hover:border-border-strong hover:shadow-sm transition-all duration-300">
                @if ($related->category)
                    <p class="text-xs text-primary mb-2">{{ $related->category->name }}</p>
                @endif
                <p class="text-sm font-bold text-text group-hover:text-primary transition leading-snug line-clamp-2 mb-2">{{ $related->title }}</p>
                <p class="text-xs text-text-muted">{{ $related->created_at->format('Y-m-d') }}</p>
            </a>
        @endforeach
    </div>
</section>
@endif
