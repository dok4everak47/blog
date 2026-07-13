@extends('layouts.blog')
@section('title', '全部文章 · My Blog')

@section('seo')
<meta name="description" content="浏览全部文章 — {{ config('app.name', 'My Blog') }}">
<meta property="og:type" content="website">
<meta property="og:title" content="全部文章">
<meta property="og:url" content="{{ route('notes.index') }}">
@endsection

@section('content')
  <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
    <x-breadcrumb :items="[['label' => '全部文章']]" />
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
      <article class="group rounded-2xl border border-border bg-surface overflow-hidden hover:border-border-strong hover:shadow-md transition-all duration-300 mb-6">
        <div onclick="window.location.href='{{ route('notes.show', $note) }}'" class="grid grid-cols-1 sm:grid-cols-5 cursor-pointer" role="link" tabindex="0">
          {{-- 左侧：文字信息 --}}
          <div class="p-6 sm:p-8 sm:col-span-3 flex flex-col justify-center">
            {{-- 日期 --}}
            <div class="flex items-center gap-1.5 text-xs text-text-muted mb-3">
              <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 2v3M16 2v3M3.5 9h17M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"/>
              </svg>
              <span>发布于 {{ $note->created_at->format('Y-m-d') }}</span>
            </div>

            {{-- 标题 --}}
            <h3 class="text-xl sm:text-2xl font-bold text-text group-hover:text-primary transition leading-snug mb-3">
              {{ $note->title }}
            </h3>

            {{-- 分类 & 标签 --}}
            <div class="flex flex-wrap items-center gap-1.5 text-xs text-text-secondary mb-3">
              @if ($note->category)
                <a href="{{ route('categories.show', $note->category) }}"
                   class="inline-flex items-center gap-1 hover:text-primary transition"
                   onclick="event.stopPropagation()">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M19 11H5m14 0a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2m14 0V9a2 2 0 0 0-2-2M5 11V9a2 2 0 0 1 2-2m0 0V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2M7 7h10"/>
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
                     class="inline-flex items-center gap-1 hover:text-primary transition"
                     onclick="event.stopPropagation()">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 0 1 0 2.828l-7 7a2 2 0 0 1-2.828 0l-7-7A2 2 0 0 1 3 12V7a4 4 0 0 1 4-4z"/>
                    </svg>
                    {{ $tag->name }}
                  </a>
                  @if(!$loop->last)
                    <span class="text-border-strong">·</span>
                  @endif
                @endforeach
              @endif
            </div>

            {{-- 摘要 --}}
            <p class="text-sm text-text-secondary line-clamp-2 leading-relaxed">
              {{ strip_tags(Str::markdown($note->content ?? '')) }}
            </p>
          </div>

          {{-- 右侧：封面图 / 内容首图 --}}
          @php
            // 优先用缩略图，其次封面图，最后从正文提取第一张 Markdown 图片
            $displayImage = $note->thumbnail_url ?: $note->cover_image_url;
            if (!$displayImage && $note->content) {
              if (preg_match('/!\[.*?\]\(([^)]+)\)/', $note->content, $m)) {
                $displayImage = $m[1];
              }
            }
          @endphp

          <div class="min-h-[200px] sm:min-h-auto sm:col-span-2 relative overflow-hidden bg-surface-2">
            @if ($displayImage)
              <img src="{{ $displayImage }}" alt="{{ $note->title }}"
                   loading="lazy"
                   class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            @else
              {{-- 无封面也无内容图时的兜底占位 --}}
              <div class="absolute inset-0 bg-gradient-to-br from-sage-light/60 to-sage/20 flex items-center justify-center">
                <div class="text-center">
                  <div class="w-20 h-20 rounded-2xl bg-white/60 backdrop-blur-sm flex items-center justify-center mx-auto mb-3 shadow-sm">
                    <span class="text-3xl font-bold text-sage">
                      {{ mb_substr($note->title, 0, 1) }}
                    </span>
                  </div>
                  <span class="text-xs text-sage/70 font-medium">点击阅读</span>
                </div>
              </div>
            @endif
          </div>
        </div>
      </article>
    @empty
      @php
        $emptyActionText = auth()->check() ? '写第一篇文章' : '登录开始阅读';
        $emptyActionUrl = auth()->check() ? route('notes.create') : route('login');
      @endphp
      <x-empty-state icon="article" title="还没有文章" description="写下你的第一篇文章吧"
          action-text="{{ $emptyActionText }}" :action-url="$emptyActionUrl" />
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
@endsection
