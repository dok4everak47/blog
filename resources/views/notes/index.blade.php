<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>全部文章 · My Blog</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-bg min-h-screen text-text">
  <x-nav />

  <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
    {{-- 页头 --}}
    <div class="flex items-center justify-between mb-8">
      <div>
        <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-2">All Articles</p>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">全部文章</h1>
      </div>
      <span class="text-sm text-text-secondary">共 {{ $notes->total() }} 篇</span>
    </div>

    {{-- 文章列表 --}}
    @forelse ($notes as $note)
      <article class="group rounded-2xl border border-border bg-surface-2 overflow-hidden hover:border-border-strong hover:shadow-sm transition-all duration-300 mb-6">
        <a href="{{ route('notes.show', $note) }}" class="grid grid-cols-1 md:grid-cols-5">
          {{-- 文字 --}}
          <div class="p-6 sm:p-8 md:col-span-3 flex flex-col justify-center">
            <div class="flex items-center gap-2 text-xs text-text-muted mb-3">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6l4 2m6-2A10 10 0 1 1 4.5 18.5"/>
              </svg>
              <span>发布于 {{ $note->created_at->format('Y-m-d') }}</span>
            </div>

            <h3 class="text-xl sm:text-2xl font-bold text-text group-hover:text-primary transition leading-snug mb-3">
              {{ $note->title }}
            </h3>

            <p class="text-sm text-text-secondary line-clamp-2 leading-relaxed mb-4">
              {{ $note->content }}
            </p>

            <div class="flex flex-wrap items-center gap-2 text-xs text-text-secondary">
              @if ($note->category)
                <a href="{{ route('categories.show', $note->category) }}"
                   class="inline-flex items-center gap-1 hover:text-primary transition"
                   onclick="event.stopPropagation()">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2m14 0V9a2 2 0 0 0-2-2M5 11V9a2 2 0 0 1 2-2m0 0V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2M7 7h10"/>
                  </svg>
                  {{ $note->category->name }}
                </a>
              @endif
              @if ($note->tags->isNotEmpty())
                @if ($note->category)
                  <span class="text-border-strong">·</span>
                @endif
                @foreach ($note->tags->take(3) as $tag)
                  <a href="{{ route('tags.show', $tag) }}"
                     class="hover:text-primary transition"
                     onclick="event.stopPropagation()">{{ $tag->name }}</a>
                @endforeach
              @endif
            </div>
          </div>

          {{-- 封面图 / 占位 --}}
          <div class="md:col-span-2 min-h-[180px] md:min-h-full relative overflow-hidden">
            @if ($note->cover_image_url)
              <img src="{{ $note->cover_image_url }}" alt="{{ $note->title }}"
                   loading="lazy"
                   class="absolute inset-0 w-full h-full object-cover">
            @else
              <div class="bg-sage-light min-h-[180px] md:min-h-full flex items-center justify-center relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-sage/10 to-transparent"></div>
                <div class="relative z-10 text-center">
                  <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-sage/20 flex items-center justify-center mx-auto mb-2">
                    <span class="text-2xl sm:text-3xl font-bold text-sage">
                      {{ mb_substr($note->title, 0, 1) }}
                    </span>
                  </div>
                  <span class="text-xs text-sage font-medium">Read more</span>
                </div>
              </div>
            @endif
          </div>
        </a>
      </article>
    @empty
      <div class="rounded-2xl border border-dashed border-border p-16 text-center bg-surface-2">
        <p class="text-text-secondary mb-2">还没有文章</p>
        @auth
          <a href="{{ route('notes.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-hover transition">
            写第一篇文章
          </a>
        @else
          <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-hover transition">
            注册账号开始写作
          </a>
        @endauth
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

        <span class="px-4 py-2 text-sm text-text bg-primary text-white rounded-lg">
          {{ $notes->currentPage() }} / {{ $notes->lastPage() }}
        </span>

        @if ($notes->hasMorePages())
          <a href="{{ $notes->nextPageUrl() }}" class="px-3 py-2 text-sm text-text-secondary rounded-lg border border-border bg-surface-2 hover:text-primary hover:border-primary transition">下一页 →</a>
        @else
          <span class="px-3 py-2 text-sm text-text-muted rounded-lg border border-border bg-surface-2 cursor-not-allowed">下一页 →</span>
        @endif
      </nav>
    @endif
  </main>
</body>
</html>
