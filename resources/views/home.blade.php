@extends('layouts.blog')
@section('title', 'My Blog — 记录生活与思考的每一刻')

@section('seo')
<meta name="description" content="{{ config('app.name', 'My Blog') }} — 个人博客，记录生活、技术与思考。分享文章、灵感与日常。">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ config('app.name', 'My Blog') }}">
<meta property="og:description" content="个人博客，记录生活、技术与思考。">
<meta property="og:url" content="{{ url('/') }}">
<meta property="og:site_name" content="{{ config('app.name', 'My Blog') }}">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ config('app.name', 'My Blog') }}">
<meta name="twitter:description" content="个人博客，记录生活、技术与思考。">
@endsection

@section('content')

{{-- ====== Hero 全屏区域 ====== --}}
<section class="hero-section" id="hero">
  {{-- 背景图层 --}}
  <div class="hero-bg">
    @php
      // 优先使用手动设置的 Hero 背景图，否则取最新文章的封面/正文首图
      $heroImage = \App\Models\SiteSetting::get('hero_image');
      if (!$heroImage && $notes->isNotEmpty()) {
        $latest = $notes->first();
        $heroImage = $latest->cover_image_url;
        if (!$heroImage && $latest->content) {
          if (preg_match('/!\[.*?\]\(([^)]+)\)/', $latest->content, $m)) {
            $heroImage = $m[1];
          }
        }
      }
    @endphp
    @if ($heroImage)
      <img src="{{ $heroImage }}" alt="hero background">
    @else
      <div class="hero-bg-fallback"></div>
    @endif
  </div>

  {{-- 暗色遮罩 --}}
  <div class="hero-overlay"></div>

  {{-- 中心内容 --}}
  <div class="hero-content">
    <h1 class="hero-title hero-animate-fade-in-down">My Blog</h1>

    <div class="hero-quote hero-animate-slit-in">
      <svg class="hero-quote-icon" fill="currentColor" viewBox="0 0 24 24">
        <path d="M7.17 6A5.001 5.001 0 0 0 2 11v7h7v-7H5.5a3.5 3.5 0 0 1 3.5-3.5V6h-1.83zm12 0A5.001 5.001 0 0 0 14 11v7h7v-7h-3.5a3.5 3.5 0 0 1 3.5-3.5V6h-1.83z"/>
      </svg>
      <span class="hero-quote-text">万物合鸣 · 独守一荒 —— 记录生活与思考的每一刻</span>
      <svg class="hero-quote-icon" fill="currentColor" viewBox="0 0 24 24" style="margin-top: auto; margin-bottom: 0.15rem;">
        <path d="M16.83 18A5.001 5.001 0 0 0 22 13V6h-7v7h3.5a3.5 3.5 0 0 1-3.5 3.5V18h1.83zm-12 0A5.001 5.001 0 0 0 10 13V6H3v7h3.5a3.5 3.5 0 0 1-3.5 3.5V18h1.83z"/>
      </svg>
    </div>

    <div class="hero-social hero-animate-fade-in-up">
      <a href="{{ route('notes.index') }}" aria-label="文章" title="全部文章">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
      </a>
      <a href="{{ route('about') }}" aria-label="关于" title="关于">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
      </a>
      <a href="{{ route('contact') }}" aria-label="联系" title="Contact">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
      </a>
    </div>
  </div>

  {{-- 向下滚动按钮 --}}
  <button class="hero-scroll-down hero-animate-float" onclick="document.getElementById('hero-content').scrollIntoView({behavior:'smooth'})" aria-label="向下滚动">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
    </svg>
  </button>

  {{-- 底部波浪 --}}
  <div class="hero-waves">
    <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M0,40 C240,80 480,0 720,40 C960,80 1200,0 1440,40 L1440,80 L0,80 Z" fill="#FDFCFA"/>
    </svg>
  </div>
</section>

{{-- ====== Hero 下方内容区 ====== --}}
<div class="hero-below-content" id="hero-content">
  <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
    {{-- Top Section: Welcome + Featured --}}
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-12">
      {{-- Welcome Card --}}
      <div class="rounded-2xl border border-border bg-surface-2 p-8 sm:p-10 flex flex-col justify-center">
        <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-4">Welcome</p>
        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight mb-3">My Blog</h1>
        <p class="text-sm text-text-secondary mb-6">Personal Blog</p>
        <div class="border-t border-border pt-5 mt-auto">
          <p class="text-xs font-medium tracking-[0.15em] text-primary uppercase mb-2">Overview</p>
          <p class="text-sm text-text-secondary leading-relaxed">
            记录生活与思考<br>
            分享技术与感悟
          </p>
        </div>
      </div>

      {{-- Featured Card --}}
      <div
        class="rounded-2xl border border-border bg-surface-2 p-8 sm:p-10 flex flex-col justify-between overflow-hidden relative">
        <div class="relative z-10">
          <p class="text-xs font-medium tracking-[0.2em] text-gold uppercase mb-4">Featured</p>
          <div class="flex items-baseline gap-3 mb-2">
            <span class="text-6xl sm:text-7xl font-extrabold text-gold leading-none">{{ date('Y') }}</span>
            <span class="text-lg text-text-secondary">无限进步~</span>
          </div>
        </div>

        @if ($notes->isNotEmpty())
          @php $featured = $notes->first(); @endphp
          <a href="{{ route('notes.show', $featured) }}" class="relative z-10 mt-8 group">
            <p class="text-xs text-text-muted mb-2">{{ $featured->created_at->format('Y-m-d') }}</p>
            <h2 class="text-lg font-bold text-text group-hover:text-primary transition leading-snug mb-2">
              {{ $featured->title }}
            </h2>
            <p class="text-sm text-text-secondary line-clamp-2 leading-relaxed">
              {{ $featured->content }}
            </p>
          </a>
        @else
          <div class="relative z-10 mt-8">
            <p class="text-sm text-text-secondary">还没有精选文章，快去写一篇吧。</p>
          </div>
        @endif

        {{-- Decorative airplane-like shape --}}
        <div class="absolute bottom-6 right-6 opacity-10">
          <svg width="120" height="60" viewBox="0 0 120 60" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 30H110M110 30L85 15M110 30L85 45" stroke="#C9A66B" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />
            <circle cx="110" cy="30" r="4" fill="#C9A66B" />
          </svg>
        </div>
      </div>
    </section>

    {{-- Latest Articles --}}
    <section>
      <div class="flex items-center justify-between mb-8">
        <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase">Latest Articles</p>
        <a href="{{ route('notes.index') }}" class="text-sm text-text-secondary hover:text-primary transition">查看全部
          →</a>
      </div>

      <div class="space-y-6">
        @forelse ($notes as $note)
          <article
            class="group rounded-2xl border border-border bg-surface-2 overflow-hidden hover:border-border-strong transition-all duration-300">
            <div onclick="window.location.href='{{ route('notes.show', $note) }}'" class="grid grid-cols-1 md:grid-cols-5 cursor-pointer" role="link">
              {{-- Text --}}
              <div class="p-6 sm:p-8 md:col-span-3 flex flex-col justify-center">
                <div class="flex items-center gap-1.5 text-xs text-text-muted mb-3">
                  <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 2v3M16 2v3M3.5 9h17M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z" />
                  </svg>
                  <span>编辑于 {{ $note->created_at->format('Y-m-d') }}</span>
                </div>

                <h3 class="text-xl sm:text-2xl font-bold text-text group-hover:text-primary transition leading-snug mb-3">
                  {{ $note->title }}
                </h3>

                <p class="text-sm text-text-secondary line-clamp-2 leading-relaxed mb-4">
                  {{ $note->excerpt ?: \App\Models\Note::generateExcerpt($note->content, 120) }}
                </p>

                <div class="flex flex-wrap items-center gap-2 text-xs text-text-secondary">
                  @if ($note->category)
                    <span class="inline-flex items-center gap-1">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M19 11H5m14 0a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2m14 0V9a2 2 0 0 0-2-2M5 11V9a2 2 0 0 1 2-2m0 0V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2M7 7h10" />
                      </svg>
                      {{ $note->category->name }}
                    </span>
                  @endif
                  @if ($note->tags->isNotEmpty())
                    @if ($note->category)
                      <span class="text-border-strong">·</span>
                    @endif
                    @foreach ($note->tags->take(3) as $tag)
                      <span>{{ $tag->name }}</span>
                    @endforeach
                  @endif
                </div>
              </div>

              {{-- Cover Image / 内容首图 --}}
              @php
                // 优先用缩略图，其次封面图，最后从正文提取第一张 Markdown 图片
                $homeDisplayImage = $note->thumbnail_url ?: $note->cover_image_url;
                if (!$homeDisplayImage && $note->content) {
                  if (preg_match('/!\[.*?\]\(([^)]+)\)/', $note->content, $hm)) {
                    $homeDisplayImage = $hm[1];
                  }
                }
              @endphp
              <div class="md:col-span-2 min-h-[180px] md:min-h-full relative overflow-hidden">
                @if ($homeDisplayImage)
                  <img src="{{ $homeDisplayImage }}" alt="{{ $note->title }}"
                    class="absolute inset-0 w-full h-full object-cover">
                @else
                  <div
                    class="bg-sage-light min-h-[180px] md:min-h-full flex items-center justify-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-sage/10 to-transparent"></div>
                    <div class="relative z-10 text-center">
                      <div
                        class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-sage/20 flex items-center justify-center mx-auto mb-2">
                        <span class="text-2xl sm:text-3xl font-bold text-sage">
                          {{ mb_substr($note->title, 0, 1) }}
                        </span>
                      </div>
                      <span class="text-xs text-sage font-medium">Read more</span>
                    </div>
                  </div>
                @endif
              </div>
            </div>
          </article>
        @empty
          <div class="rounded-2xl border border-dashed border-border p-16 text-center bg-surface-2">
            <p class="text-text-secondary mb-4">还没有文章</p>
            @auth
              <a href="{{ route('notes.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-hover transition">
                写第一篇文章
              </a>
            @else
              <a href="{{ route('register') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-hover transition">
                注册账号开始写作
              </a>
            @endauth
          </div>
        @endforelse
      </div>
    </section>

    {{-- Categories & Tags --}}
    <section class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-12">
      <div class="rounded-2xl border border-border bg-surface-2 p-6 sm:p-8">
        <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-5">Categories</p>
        <div class="space-y-3">
          @foreach ($categories as $category)
            <a href="{{ route('categories.show', $category) }}"
              class="flex items-center justify-between text-sm group/cat hover:text-primary transition">
              <span class="text-text group-hover/cat:text-primary">{{ $category->name }}</span>
              <span
                class="text-xs text-text-secondary bg-surface px-2 py-0.5 rounded-full group-hover/cat:bg-primary-light group-hover/cat:text-primary">{{ $category->notes_count }}
                篇</span>
            </a>
          @endforeach
        </div>
      </div>

      <div class="rounded-2xl border border-border bg-surface-2 p-6 sm:p-8">
        <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-5">Tags</p>
        <div class="flex flex-wrap gap-2">
          @foreach ($tags as $tag)
            <a href="{{ route('tags.show', $tag) }}"
              class="rounded-full bg-surface px-3 py-1 text-xs text-text-secondary border border-border hover:border-primary hover:text-primary transition">
              {{ $tag->name }}
            </a>
          @endforeach
        </div>
      </div>
    </section>
  </main>
</div>

@endsection
