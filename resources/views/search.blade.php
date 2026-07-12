@extends('layouts.blog')
@section('title', '搜索' . ($q ? '：' . $q : '') . ' · My Blog')

@section('content')
  <main class="max-w-4xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    {{-- 页头 + 搜索框 --}}
    <div class="mb-10">
      <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-2">Search</p>
      <h1 class="text-2xl sm:text-3xl font-bold tracking-tight mb-6">搜索文章</h1>

      <form action="{{ route('search') }}" method="GET" class="relative">
        <input type="text" name="q" value="{{ $q }}" autofocus
               placeholder="输入关键词搜索文章标题或正文…"
               class="w-full rounded-xl border border-border bg-surface-2 px-5 py-3.5 pr-12 text-sm text-text outline-none transition focus:border-primary focus:bg-surface-2 focus:ring-2 focus:ring-primary/10">
        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 p-2 text-text-secondary hover:text-primary transition" aria-label="搜索">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </button>
      </form>
    </div>

    {{-- 结果 --}}
    @if ($q !== '')
      <p class="text-sm text-text-secondary mb-6">
        找到 <span class="font-medium text-text">{{ $notes->total() }}</span> 篇与「<span class="text-primary">{{ $q }}</span>」相关的文章
      </p>

      @forelse ($notes as $note)
        <article class="group rounded-2xl border border-border bg-surface-2 p-6 sm:p-8 hover:border-border-strong hover:shadow-sm transition-all duration-300 mb-5">
          <a href="{{ route('notes.show', $note) }}">
            <div class="flex items-center gap-2 text-xs text-text-muted mb-2">
              <span>{{ $note->created_at->format('Y-m-d') }}</span>
              @if ($note->category)
                <span class="text-border-strong">·</span>
                <span class="text-primary">{{ $note->category->name }}</span>
              @endif
            </div>
            <h3 class="text-lg sm:text-xl font-bold text-text group-hover:text-primary transition leading-snug mb-2">
              {{ $note->title }}
            </h3>
            <p class="text-sm text-text-secondary line-clamp-2 leading-relaxed">
              {{ $note->excerpt ?: \App\Models\Note::generateExcerpt($note->content, 120) }}
            </p>
          </a>
        </article>
      @empty
        <div class="rounded-2xl border border-dashed border-border p-16 text-center bg-surface-2">
          <p class="text-text-secondary mb-2">没有找到相关文章</p>
          <p class="text-xs text-text-muted">试试换个关键词？</p>
        </div>
      @endforelse

      {{-- 分页 --}}
      @if ($notes->hasPages())
        <nav class="mt-10 flex items-center justify-center gap-2" aria-label="分页">
          @if ($notes->onFirstPage())
            <span class="px-3 py-2 text-sm text-text-muted rounded-lg border border-border bg-surface-2 cursor-not-allowed">← 上一页</span>
          @else
            <a href="{{ $notes->previousPageUrl() }}" class="px-3 py-2 text-sm text-text-secondary rounded-lg border border-border bg-surface-2 hover:text-primary hover:border-primary transition">← 上一页</a>
          @endif
          <span class="px-4 py-2 text-sm text-text bg-primary text-white rounded-lg">{{ $notes->currentPage() }} / {{ $notes->lastPage() }}</span>
          @if ($notes->hasMorePages())
            <a href="{{ $notes->nextPageUrl() }}" class="px-3 py-2 text-sm text-text-secondary rounded-lg border border-border bg-surface-2 hover:text-primary hover:border-primary transition">下一页 →</a>
          @else
            <span class="px-3 py-2 text-sm text-text-muted rounded-lg border border-border bg-surface-2 cursor-not-allowed">下一页 →</span>
          @endif
        </nav>
      @endif
    @else
      <div class="rounded-2xl border border-dashed border-border p-16 text-center bg-surface-2">
        <p class="text-text-secondary">输入关键词开始搜索</p>
      </div>
    @endif
  </main>
@endsection
