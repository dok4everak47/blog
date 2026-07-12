@extends('layouts.blog')
@section('title', '关于 · My Blog')

@section('seo')
<meta name="description" content="关于我 — {{ config('app.name', 'My Blog') }}">
<meta property="og:type" content="profile">
<meta property="og:title" content="关于">
<meta property="og:url" content="{{ route('about') }}">
@endsection

@section('content')
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
      <section class="mb-10">
        <p class="text-text-secondary leading-[1.85] text-[15px]">
          hello，很高兴遇见你，陌生人。相见即是幸运，那下面是关于我的一些介绍 😊
        </p>
      </section>

      {{-- 虚线分隔 --}}
      <hr class="border-dashed border-border my-10">

      {{-- 个人信息 --}}
      <section class="mb-10">
        <h2 class="text-xl font-bold text-text flex items-center gap-2 mb-6">
          <span class="text-primary">♦</span>
          关于·自我
        </h2>

        <div class="space-y-4">
          {{-- 称呼 --}}
          <div class="flex items-start gap-3">
            <span class="shrink-0 mt-0.5">👋</span>
            <div class="leading-relaxed">
              <span class="text-primary font-medium">称呼：</span>
              <span class="text-text">你可以称我为 Dok4ever 或 4ever，当然也可以叫我别的~</span>
            </div>
          </div>

          {{-- 身份 --}}
          <div class="flex items-start gap-3">
            <span class="shrink-0 mt-0.5">🎓</span>
            <div class="leading-relaxed">
              <span class="text-primary font-medium">身份：</span>
              <span class="text-text">应届毕业生 / Laravel 学习者</span>
            </div>
          </div>

          {{-- 坐标 --}}
          <div class="flex items-start gap-3">
            <span class="shrink-0 mt-0.5">🌍</span>
            <div class="leading-relaxed">
              <span class="text-primary font-medium">坐标：</span>
              <span class="text-text">中国 · 广西</span>
            </div>
          </div>

          {{-- 状态 --}}
          <div class="flex items-start gap-3">
            <span class="shrink-0 mt-0.5">🌱</span>
            <div class="leading-relaxed">
              <span class="text-primary font-medium">目前状态：</span>
              <span class="text-text">正在从零学习 Laravel，用这个博客记录学习过程</span>
            </div>
          </div>

          {{-- 标签 --}}
          <div class="flex items-start gap-3">
            <span class="shrink-0 mt-0.5">🏷️</span>
            <div class="leading-relaxed">
              <span class="text-primary font-medium">标签：</span>
              <span class="text-text">Laravel 初学者 | PHP 新手 | 博客搭建中 | 技术爱好者 | 写作练习生</span>
            </div>
          </div>
        </div>
      </section>

      {{-- 虚线分隔 --}}
      <hr class="border-dashed border-border my-10">

      {{-- 技术栈 / 兴趣 --}}
      <section class="mb-10">
        <h2 class="text-xl font-bold text-text flex items-center gap-2 mb-6">
          <span class="text-primary">♦</span>
          技术·兴趣
        </h2>

        <div class="space-y-4">
          <div class="leading-relaxed">
            <span class="text-primary font-medium">后端框架：</span>
            <span class="text-text">Laravel 13（正在深入学习中～）</span>
          </div>
          <div class="leading-relaxed">
            <span class="text-primary font-medium">前端技术：</span>
            <span class="text-text">Tailwind CSS v4 · Alpine.js · Vite</span>
          </div>
          <div class="leading-relaxed">
            <span class="text-primary font-medium">语言基础：</span>
            <span class="text-text">PHP 8.3 · 正在补齐 JavaScript 和 SQL</span>
          </div>
          <div class="leading-relaxed">
            <span class="text-primary font-medium">其他兴趣：</span>
            <span class="text-text">Agent 工程 · 强化学习研究 · 数据库自治方向探索</span>
          </div>
          <div class="leading-relaxed">
            <span class="text-primary font-medium">目标：</span>
            <span class="text-text">把 Laravel 学精，做出有个人风格的博客系统 ✨</span>
          </div>
        </div>
      </section>

      {{-- 虚线分隔 --}}
      <hr class="border-dashed border-border my-10">

      {{-- 联系方式 --}}
      <section class="mb-10">
        <h2 class="text-xl font-bold text-text flex items-center gap-2 mb-6">
          <span class="text-primary">♦</span>
          关于·联系
        </h2>

        <div class="space-y-3 text-text-secondary leading-[1.85]">
          <p>如果想交换友链可以去友链界面；</p>
          <p>和我临时聊天可以去留言板；</p>
          <p>如有任何问题欢迎给我发邮件（邮箱：<a href="mailto:girlsfrontline45@gmail.com" class="text-primary hover:text-primary-hover transition underline decoration-primary/30 hover:decoration-primary/60">girlsfrontline45@gmail.com</a>）。</p>
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
        {{-- 背景：用最新文章封面图或渐变兜底 --}}
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
          <a href="#tech"
             class="flex items-center gap-2.5 text-sm py-2 px-3 rounded-lg border-l-[3px] border-transparent text-text-secondary hover:text-text hover:border-border-strong hover:bg-surface-2 transition">
            <span>⚡</span> 技术·兴趣
          </a>
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
