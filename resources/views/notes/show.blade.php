@extends('layouts.blog')
@section('title', ($note->title ?? '') . ' · My Blog')

@php
$seoDescription = $note->excerpt
    ?? \App\Models\Note::generateExcerpt($note->content, 160);
$ogImage = $note->cover_image_url
    ?? (preg_match('/!\[.*?\]\(([^)]+)\)/', $note->content ?? '', $m) ? $m[1] : null);
@endphp

@section('seo')
<meta name="description" content="{{ $seoDescription }}">
<meta property="og:type" content="article">
<meta property="og:title" content="{{ $note->title }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ route('notes.show', $note) }}">
@if($ogImage)
<meta property="og:image" content="{{ url($ogImage) }}">
@endif
<meta property="og:site_name" content="{{ config('app.name', 'My Blog') }}">
<meta property="article:published_time" content="{{ $note->created_at->toIso8601String() }}">
@if($note->updated_at->gt($note->created_at))
<meta property="article:modified_time" content="{{ $note->updated_at->toIso8601String() }}">
@endif
<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $note->title }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
@if($ogImage)
<meta name="twitter:image" content="{{ url($ogImage) }}">
@endif
@endsection

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
             class="rounded-full bg-surface-2 border border-border px-3 py-1 text-xs text-text-secondary hover:border-primary hover:text-primary transition">
            {{ $tag->name }}
          </a>
        @endforeach
      </div>
    @endif

    {{-- 评论区 --}}
    <section class="mt-10 pt-10 border-t border-border" id="comments">
      @php
        $comments = $note->loadCount('comments')->comments;
      @endphp
      <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-5">
        评论（{{ $note->comments_count }}）
      </p>

      {{-- 评论列表 --}}
      @if($comments->isEmpty())
        <p class="text-text-muted text-sm py-6">暂无评论，来发表第一条吧~</p>
      @else
        <div class="space-y-5 mb-8">
          @foreach($comments as $comment)
            <div class="flex gap-3" id="comment-{{ $comment->id }}">
              <div class="w-8 h-8 rounded-full bg-primary flex-shrink-0 flex items-center justify-center text-white text-xs font-bold">
                {{ strtoupper(substr($comment->user->name, 0, 1)) }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-sm font-medium text-text">{{ $comment->user->name }}</span>
                  <span class="text-xs text-text-muted">{{ $comment->created_at->diffForHumans() }}</span>
                  @auth && $comment->user_id === auth()->id()
                    <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="ml-auto">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="text-xs text-text-muted hover:text-red-500 transition">删除</button>
                    </form>
                  @endauth
                </div>
                <p class="text-sm text-text-secondary leading-relaxed">{{ $comment->content }}</p>

                {{-- 回复列表 --}}
                @if($comment->replies->isNotEmpty())
                  <div class="mt-3 ml-4 space-y-3 pl-3 border-l-2 border-border">
                    @foreach($comment->replies as $reply)
                      <div class="flex gap-2">
                        <div class="w-6 h-6 rounded-full bg-sage flex-shrink-0 flex items-center justify-center text-white text-[10px] font-bold">
                          {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                        </div>
                        <div>
                          <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-xs font-medium text-text">{{ $reply->user->name }}</span>
                            <span class="text-[10px] text-text-muted">{{ $reply->created_at->diffForHumans() }}</span>
                          </div>
                          <p class="text-xs text-text-secondary leading-relaxed">{{ $reply->content }}</p>
                        </div>
                      </div>
                    @endforeach
                  </div>
                @endif

                {{-- 回复按钮 --}}
                @auth
                  <button onclick="document.getElementById('reply-form-{{ $comment->id }}').classList.toggle('hidden')"
                          class="mt-1 text-xs text-text-muted hover:text-primary transition">回复</button>
                  <form id="reply-form-{{ $comment->id }}" class="hidden mt-2"
                        action="{{ route('comments.store', $note) }}" method="POST">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                    <div class="flex gap-2">
                      <input name="content" required maxlength="2000" placeholder="写下你的回复…"
                             class="flex-1 rounded-lg border border-border bg-surface px-3 py-1.5 text-sm text-text outline-none focus:border-primary">
                      <button type="submit" class="px-3 py-1.5 rounded-lg bg-primary text-white text-xs font-medium hover:bg-primary-hover transition">回复</button>
                    </div>
                  </form>
                @endauth
              </div>
            </div>
          @endforeach
        </div>
      @endif

      {{-- 发表评论表单 --}}
      @auth
        <form action="{{ route('comments.store', $note) }}" method="POST" class="mt-4">
          @csrf
          <textarea name="content" required maxlength="2000" rows="3" placeholder="发表评论…" 
                    class="w-full rounded-xl border border-border bg-surface px-4 py-3 text-sm text-text outline-none focus:border-primary resize-y"></textarea>
          <div class="mt-2 flex justify-end">
            <button type="submit" class="px-5 py-2 rounded-lg bg-primary text-white text-sm font-medium hover:bg-primary-hover transition">发布评论</button>
          </div>
        </form>
      @else
        <p class="mt-4 text-sm text-text-secondary">
          <a href="{{ route('login') }}" class="text-primary hover:underline">登录</a> 后参与评论
        </p>
      @endauth
    </section>

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
