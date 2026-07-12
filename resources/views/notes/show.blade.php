@extends('layouts.blog')
@section('title', ($note->title ?? '') . ' · My Blog')

@section('content')
  <main class="max-w-2xl mx-auto px-4 sm:px-6 py-16 sm:py-20">
    <article>
      <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-4">文章</p>

      <h1 class="text-3xl sm:text-4xl font-bold tracking-tight mb-5 leading-tight">{{ $note->title }}</h1>

      @if ($note->cover_image_url)
        <div class="mb-8 rounded-2xl overflow-hidden border border-border">
          <img src="{{ $note->cover_image_url }}" alt="{{ $note->title }}"
               class="w-full h-60 sm:h-80 object-cover">
        </div>
      @endif

      <div class="flex flex-wrap items-center gap-3 mb-8 text-sm text-text-secondary">
        <span class="inline-flex items-center gap-1">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6l4 2m6-2A10 10 0 1 1 4.5 18.5"/>
          </svg>
          {{ $note->created_at->format('Y-m-d') }}
        </span>
        @if ($note->category)
          <span class="text-border-strong">·</span>
          <span class="text-primary">{{ $note->category->name }}</span>
        @endif
      </div>

      <div class="border-t border-border pt-8">
        <div class="article-content text-text leading-relaxed text-[15px]">
          {!! Str::markdown($note->content, [
              'html_input' => 'strip',
              'allow_unsafe_links' => false,
              'max_nesting_level' => 20,
          ]) !!}
        </div>
      </div>
    </article>

    @if ($note->tags->isNotEmpty())
      <div class="mt-8 flex flex-wrap gap-2">
        @foreach ($note->tags as $tag)
          <a href="{{ route('tags.show', $tag) }}"
             class="rounded-full bg-white border border-border px-3 py-1 text-xs text-text-secondary hover:border-primary hover:text-primary transition">
            {{ $tag->name }}
          </a>
        @endforeach
      </div>
    @endif

    {{-- 上一篇 / 下一篇 --}}
    @if ($previous || $next)
      <nav class="mt-10 grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if ($previous)
          <a href="{{ route('notes.show', $previous) }}"
             class="group rounded-xl border border-border bg-surface-2 p-4 hover:border-primary transition">
            <p class="text-xs text-text-muted mb-1">← 上一篇</p>
            <p class="text-sm font-medium text-text group-hover:text-primary transition line-clamp-1">{{ $previous->title }}</p>
          </a>
        @else
          <span></span>
        @endif
        @if ($next)
          <a href="{{ route('notes.show', $next) }}"
             class="group rounded-xl border border-border bg-surface-2 p-4 hover:border-primary transition text-right">
            <p class="text-xs text-text-muted mb-1">下一篇 →</p>
            <p class="text-sm font-medium text-text group-hover:text-primary transition line-clamp-1">{{ $next->title }}</p>
          </a>
        @endif
      </nav>
    @endif

    {{-- 相关文章 --}}
    @if ($related->isNotEmpty())
      <section class="mt-12 pt-10 border-t border-border">
        <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-5">Related</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          @foreach ($related as $rel)
            <a href="{{ route('notes.show', $rel) }}"
               class="group rounded-xl border border-border bg-surface-2 p-5 hover:border-border-strong hover:shadow-sm transition-all duration-300">
              @if ($rel->category)
                <p class="text-xs text-primary mb-2">{{ $rel->category->name }}</p>
              @endif
              <p class="text-sm font-bold text-text group-hover:text-primary transition leading-snug line-clamp-2 mb-2">{{ $rel->title }}</p>
              <p class="text-xs text-text-muted">{{ $rel->created_at->format('Y-m-d') }}</p>
            </a>
          @endforeach
        </div>
      </section>
    @endif

    <div class="mt-10 flex flex-wrap items-center gap-4 text-sm pt-8 border-t border-border">
      <a href="{{ route('home') }}" class="text-text-secondary hover:text-primary transition">← 回首页</a>
      @auth
        <a href="{{ route('notes.edit', $note) }}" class="text-primary hover:text-primary-hover transition">编辑</a>
        <form action="{{ route('notes.destroy', $note) }}" method="POST" class="inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="text-red-600 hover:text-red-700 transition"
            onclick="return confirm('确定要删除这篇文章吗？')">删除</button>
        </form>
      @endauth
    </div>
  </main>
@endsection
