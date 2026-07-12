@extends('layouts.blog')
@section('title', 'My Blog')

@section('content')
  <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
    {{-- Top Section: Welcome + Featured --}}
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-12">
      {{-- Welcome Card --}}
      <div class="rounded-2xl border border-border bg-surface-2 p-8 sm:p-10 flex flex-col justify-center">
        <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-4">Welcome</p>
        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight mb-3">合荒小站</h1>
        <p class="text-sm text-text-secondary mb-6">HeuhangSite</p>
        <div class="border-t border-border pt-5 mt-auto">
          <p class="text-xs font-medium tracking-[0.15em] text-primary uppercase mb-2">Overview</p>
          <p class="text-sm text-text-secondary leading-relaxed">
            万物合鸣·独守一荒<br>
            RIM 的生活与思考
          </p>
        </div>
      </div>

      {{-- Featured Card --}}
      <div
        class="rounded-2xl border border-border bg-surface-2 p-8 sm:p-10 flex flex-col justify-between overflow-hidden relative">
        <div class="relative z-10">
          <p class="text-xs font-medium tracking-[0.2em] text-gold uppercase mb-4">Featured</p>
          <div class="flex items-baseline gap-3 mb-2">
            <span class="text-6xl sm:text-7xl font-extrabold text-gold leading-none">2026</span>
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
                <div class="flex items-center gap-2 text-xs text-text-muted mb-3">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M12 6v6l4 2m6-2A10 10 0 1 1 4.5 18.5" />
                  </svg>
                  <span>编辑于 {{ $note->created_at->format('Y-m-d') }}</span>
                </div>

                <h3 class="text-xl sm:text-2xl font-bold text-text group-hover:text-primary transition leading-snug mb-3">
                  {{ $note->title }}
                </h3>

                <p class="text-sm text-text-secondary line-clamp-2 leading-relaxed mb-4">
                  {{ $note->content }}
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

              {{-- Cover Image / Placeholder --}}
              <div class="md:col-span-2 min-h-[180px] md:min-h-full relative overflow-hidden">
                @if ($note->cover_image_url)
                  <img src="{{ $note->cover_image_url }}" alt="{{ $note->title }}"
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
@endsection
