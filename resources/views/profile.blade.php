@extends('layouts.blog')
@section('title', '关于 · My Blog')

@section('seo')
<meta name="description" content="关于我 — {{ config('app.name', 'My Blog') }}">
<meta property="og:type" content="profile">
<meta property="og:title" content="关于">
<meta property="og:url" content="{{ route('about') }}">
@endsection

@section('content')
@php
    $about = \App\Models\SiteSetting::get('about_content', []);
    $greeting = $about['greeting'] ?? 'hello，很高兴遇见你，陌生人。相见即是幸运，那下面是关于我的一些介绍 😊';
    $selfItems = $about['self'] ?? [];
    $techItems = $about['tech'] ?? [];
    $contactIntro = $about['contact_intro'] ?? "如有任何问题欢迎给我发邮件。";
    $email = $about['email'] ?? '';
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
  <div class="flex gap-10">

    {{-- ====== 左侧主内容区 ====== --}}
    <div class="flex-1 min-w-0">

      {{-- 页面标题 --}}
      <header class="mb-10">
        <h1 class="text-2xl sm:text-3xl font-bold text-text flex items-center gap-2.5">
          <span class="text-primary">◆</span>
          关于·自我
        </h1>
      </header>

      {{-- 开场白 --}}
      @if($greeting)
      <section class="mb-10">
        <p class="text-text-secondary leading-[1.85] text-[15px]">{!! nl2br(e($greeting)) !!}</p>
      </section>
      @endif

      {{-- 虚线分隔 --}}
      <hr class="border-dashed border-border my-10">

      {{-- 个人信息 --}}
      @if(!empty($selfItems))
      <section class="mb-10" id="self">
        <h2 class="text-xl font-bold text-text flex items-center gap-2 mb-6">
          <span class="text-primary">♦</span>
          关于·自我
        </h2>

        <div class="space-y-4">
          @foreach($selfItems as $item)
          @if(!empty($item['value']))
          <div class="flex items-start gap-3">
            <span class="shrink-0 mt-0.5">{{ $item['icon'] ?? '' }}</span>
            <div class="leading-relaxed">
              <span class="text-primary font-medium">{{ $item['label'] }}：</span>
              <span class="text-text">{{ $item['value'] }}</span>
            </div>
          </div>
          @endif
          @endforeach
        </div>
      </section>
      @endif

      {{-- 虚线分隔 --}}
      @if(!empty($techItems))
      <hr class="border-dashed border-border my-10">

      {{-- 技术栈 / 兴趣 --}}
      <section class="mb-10" id="tech">
        <h2 class="text-xl font-bold text-text flex items-center gap-2 mb-6">
          <span class="text-primary">♦</span>
          技术·兴趣
        </h2>

        <div class="space-y-4">
          @foreach($techItems as $item)
          @if(!empty($item['value']))
          <div class="leading-relaxed">
            <span class="text-primary font-medium">{{ $item['label'] }}：</span>
            <span class="text-text">{!! nl2br(e($item['value'])) !!}</span>
          </div>
          @endif
          @endforeach
        </div>
      </section>
      @endif

      {{-- 虚线分隔 --}}
      <hr class="border-dashed border-border my-10">

      {{-- 联系方式 --}}
      <section class="mb-10" id="contact">
        <h2 class="text-xl font-bold text-text flex items-center gap-2 mb-6">
          <span class="text-primary">♦</span>
          关于·联系
        </h2>

        <div class="space-y-3 text-text-secondary leading-[1.85]">
          {!! nl2br(e($contactIntro)) !!}
          @if($email)
          <p>邮箱：<a href="mailto:{{ $email }}" class="text-primary hover:text-primary-hover transition underline decoration-primary/30 hover:decoration-primary/60">{{ $email }}</a></p>
          @endif
        </div>
      </section>

      {{-- 虚线分隔 --}}
      <hr class="border-dashed border-border my-10">

     {{-- 作者信息框（左侧粉色竖边） --}}
      <aside class="rounded-lg border-l-[3px] border-primary bg-surface-2/60 p-5 sm:p-6 mb-10">
        <p class="text-sm leading-relaxed mb-2">
          <span class="font-medium text-text">本文作者：</span>
          <span class="text-text-secondary">Dok4ever</span>
        </p>
        <p class="text-sm leading-relaxed mb-2">
          <span class="font-medium text-text">本文链接：</span>
          <a href="{{ url()->current() }}" class="text-primary hover:text-primary-hover transition break-all">{{ url()->current() }}</a>
        </p>
        <p class="text-sm leading-relaxed text-text-secondary">
          <span class="font-medium text-text">版权声明：</span>
          本站所有文章除特别声明外，均采用
          <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/" target="_blank" rel="noopener noreferrer"
             class="text-primary hover:text-primary-hover transition">CC BY-NC-SA</a>
          许可协议。
        </p>
      </aside>

      {{-- 底部推荐卡 --}}
      @php
        $latestNote = \App\Models\Note::published()->with('category')->latest()->first();
      @endphp
      @if ($latestNote)
      <a href="{{ route('notes.show', $latestNote) }}"
         class="group block rounded-2xl overflow-hidden relative h-48 sm:h-56 hover:-translate-y-0.5 transition-transform duration-300">
        @if ($latestNote->thumbnail_url || $latestNote->cover_image_url)
          <img src="{{ $latestNote->thumbnail_url ?: $latestNote->cover_image_url }}"
               alt="" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-black/25 to-transparent"></div>
        @else
          <div class="absolute inset-0 bg-gradient-to-br from-sage/70 via-sage to-sage-light"></div>
        @endif

        <div class="relative z-10 h-full flex flex-col justify-end p-6 sm:p-8">
          <p class="text-[11px] font-medium tracking-[0.2em] text-white/60 uppercase mb-2">Latest Post</p>
          <h3 class="text-lg sm:text-xl font-bold text-white group-hover:text-sage-light/90 transition">
            {{ $latestNote->title }}
          </h3>
        </div>
      </a>
      @endif
    </div>

    {{-- ====== 右侧导航栏 ====== --}}
    <aside class="hidden lg:block w-52 shrink-0">
      <div class="sticky top-24">
        <p class="text-xs font-medium tracking-[0.15em] text-text-muted uppercase mb-4">页面目录</p>
        <nav class="space-y-1">
          <a href="#self"
             class="flex items-center gap-2.5 text-sm py-2 px-3 rounded-lg border-l-[3px] border-primary bg-primary-light/40 text-primary font-medium transition">
            <span>😊</span> 关于·自我
          </a>
          @if(!empty($techItems))
          <a href="#tech"
             class="flex items-center gap-2.5 text-sm py-2 px-3 rounded-lg border-l-[3px] border-transparent text-text-secondary hover:text-text hover:border-border-strong hover:bg-surface-2 transition">
            <span>⚡</span> 技术·兴趣
          </a>
          @endif
          <a href="#contact"
             class="flex items-center gap-2.5 text-sm py-2 px-3 rounded-lg border-l-[3px] border-transparent text-text-secondary hover:text-text hover:border-border-strong hover:bg-surface-2 transition">
            <span>💬</span> 关于·联系
          </a>
        </nav>
      </div>
    </aside>

  </div>
</div>
@endsection
