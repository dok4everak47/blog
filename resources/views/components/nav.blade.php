<nav x-data="{ open: false, searchOpen: false }"
     @keydown.cmd.k.prevent="searchOpen = true"
     @keydown.ctrl.k.prevent="searchOpen = true"
     @keydown.escape.window="searchOpen = false"
     class="sticky top-0 z-50 bg-surface-2/70 backdrop-blur-md border-b border-border">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      {{-- Left: Logo + public links --}}
      <div class="flex items-center gap-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0" title="Ad Fontes Codicis">
          <span class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-white text-sm font-bold">A</span>
          <span class="text-base font-bold text-text hidden sm:inline">AFC</span>
        </a>
        <div class="hidden sm:flex items-center gap-5">
          <a href="{{ route('home') }}"
            class="text-sm transition {{ request()->routeIs('home') ? 'text-primary font-medium' : 'text-text-secondary hover:text-primary' }}">
            首页
          </a>
          <a href="{{ route('notes.index') }}"
            class="text-sm transition {{ request()->routeIs('notes.index') ? 'text-primary font-medium' : 'text-text-secondary hover:text-primary' }}">
            文章
          </a>
          <a href="{{ route('about') }}"
            class="text-sm transition {{ request()->routeIs('about') ? 'text-primary font-medium' : 'text-text-secondary hover:text-primary' }}">
            关于
          </a>
          <a href="{{ route('contact') }}"
            class="text-sm transition {{ request()->routeIs('contact') ? 'text-primary font-medium' : 'text-text-secondary hover:text-primary' }}">
            Contact
          </a>
        </div>
      </div>

      {{-- 中间：搜索按钮（桌面端，点击弹出悬浮搜索框） --}}
      <div class="hidden sm:flex items-center">
        <button @click="searchOpen = true"
                class="flex items-center gap-2 rounded-lg border border-border bg-surface px-3 py-1.5 text-sm text-text-muted hover:border-primary hover:text-primary transition"
                aria-label="搜索文章">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <span>搜索文章…</span>
          <kbd class="ml-2 hidden md:inline-flex items-center gap-0.5 rounded border border-border bg-surface-2 px-1.5 py-0.5 text-[10px] text-text-muted font-mono">⌘K</kbd>
        </button>
      </div>

      {{-- 悬浮搜索弹窗（桌面端，挂在 nav 层级） --}}
      <div x-show="searchOpen"
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="opacity-0"
           x-transition:enter-end="opacity-100"
           x-transition:leave="transition ease-in duration-150"
           x-transition:leave-start="opacity-100"
           x-transition:leave-end="opacity-0"
           x-cloak
           class="fixed inset-0 z-[60] hidden sm:flex items-start justify-center pt-[15vh] px-4"
           @click.self="searchOpen = false">
        {{-- 背景遮罩 --}}
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm"></div>
        {{-- 搜索面板 --}}
        <div class="relative w-full max-w-xl rounded-2xl border border-border bg-surface-2 shadow-2xl overflow-hidden"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2">
          <form action="{{ route('search') }}" method="GET" class="flex items-center gap-3 border-b border-border px-4">
            <svg class="w-5 h-5 text-text-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="q" value="{{ request('q') }}"
                   x-ref="searchInput"
                   placeholder="搜索文章标题或正文…"
                   x-init="$watch('searchOpen', v => v ? setTimeout(() => { $refs.searchInput.focus(); $refs.searchInput.select(); }, 100) : null)"
                   class="w-full bg-transparent py-4 text-base text-text outline-none placeholder:text-text-muted">
            <button type="button" @click="searchOpen = false"
                    class="shrink-0 rounded-lg border border-border bg-surface px-2 py-1 text-xs text-text-muted hover:text-primary hover:border-primary transition">
              ESC
            </button>
          </form>
          <div class="px-4 py-3 text-xs text-text-muted">
            输入关键词后按回车搜索
          </div>
        </div>
      </div>

      {{-- Right: theme toggle + auth state --}}
      <div class="hidden sm:flex items-center gap-3">
        {{-- RSS 订阅 --}}
        <a href="{{ route('feed.rss') }}"
           class="p-2 rounded-lg text-text-secondary hover:text-primary hover:bg-surface transition"
           title="RSS 订阅"
           aria-label="RSS Feed">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 5c7.18 0 13 5.82 13 13M6 11a7 7 0 017 7m-6 0a1 1 0 11-2 0 1 1 0 012 0z"/>
          </svg>
        </a>

        {{-- 主题切换 --}}
        <button id="theme-toggle" type="button"
                class="p-2 rounded-lg text-text-secondary hover:text-primary hover:bg-surface transition"
                title="切换主题"
                aria-label="切换主题">
          <!-- sun icon (shown in dark mode) -->
          <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
          <!-- moon icon (shown in light mode) -->
          <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
          </svg>
        </button>

        @guest
          <a href="{{ route('login') }}" class="text-sm text-text-secondary hover:text-primary transition px-3 py-2">Login</a>
          @if (Route::has('register'))
            <a href="{{ route('register') }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-hover transition">Register</a>
          @endif
        @endguest

        @auth
          <a href="{{ route('notes.create') }}"
            class="rounded-lg px-4 py-2 text-sm font-medium transition {{ request()->routeIs('notes.create', 'notes.edit') ? 'bg-surface text-primary cursor-default' : 'bg-primary text-white hover:bg-primary-hover' }}"
            {{ request()->routeIs('notes.create', 'notes.edit') ? 'aria-current="page"' : '' }}>
            ✍️ 写文章
          </a>
          <a href="{{ route('dashboard') }}"
            class="text-sm transition {{ request()->routeIs('dashboard') ? 'text-primary font-medium' : 'text-text-secondary hover:text-primary' }} px-3 py-2">
            Dashboard
          </a>
          <div class="relative" x-data="{ userMenu: false }" @click.outside="userMenu = false">
            <button @click="userMenu = !userMenu" class="flex items-center gap-1 text-sm text-text-secondary hover:text-primary transition px-3 py-2">
              {{ Auth::user()->name }}
              <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
            <div x-show="userMenu" x-transition x-cloak
              class="absolute right-0 mt-2 w-44 rounded-xl border border-border bg-surface-2 shadow-sm overflow-hidden">
              <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-text hover:bg-surface hover:text-primary transition">Profile</a>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full text-left px-4 py-2.5 text-sm text-text hover:bg-surface hover:text-primary transition">Log Out</button>
              </form>
            </div>
          </div>
        @endauth
      </div>

      {{-- Mobile hamburger --}}
      <div class="sm:hidden">
        <button @click="open = !open" class="p-2 rounded-lg text-text-secondary hover:text-primary hover:bg-surface transition">
          <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>

  {{-- Mobile menu --}}
  <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden border-t border-border bg-surface-2">
    <div class="px-4 py-3 space-y-1">
      <form action="{{ route('search') }}" method="GET" class="relative mb-2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="搜索文章…"
               class="w-full rounded-lg border border-border bg-surface px-3 py-2 pl-9 text-sm text-text outline-none transition focus:border-primary focus:bg-surface-2">
        <svg class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-text-muted pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
      </form>
      <a href="{{ route('home') }}" class="block py-2 text-sm rounded-lg px-3 {{ request()->routeIs('home') ? 'text-primary font-medium bg-surface' : 'text-text-secondary' }}">首页</a>
      <a href="{{ route('notes.index') }}" class="block py-2 text-sm rounded-lg px-3 {{ request()->routeIs('notes.index') ? 'text-primary font-medium bg-surface' : 'text-text-secondary' }}">文章</a>
      <a href="{{ route('about') }}" class="block py-2 text-sm rounded-lg px-3 {{ request()->routeIs('about') ? 'text-primary font-medium bg-surface' : 'text-text-secondary' }}">关于</a>
      <a href="{{ route('contact') }}" class="block py-2 text-sm rounded-lg px-3 {{ request()->routeIs('contact') ? 'text-primary font-medium bg-surface' : 'text-text-secondary' }}">Contact</a>
      @guest
        <a href="{{ route('login') }}" class="block py-2 text-sm text-text-secondary px-3">Login</a>
        @if (Route::has('register'))
          <a href="{{ route('register') }}" class="block py-2 text-sm text-primary font-medium px-3">Register</a>
        @endif
      @endguest
      @auth
        <a href="{{ route('notes.create') }}" class="block py-2 text-sm rounded-lg px-3 {{ request()->routeIs('notes.create', 'notes.edit') ? 'text-primary font-medium bg-surface' : 'text-text-secondary' }}" {{ request()->routeIs('notes.create', 'notes.edit') ? 'aria-current="page"' : '' }}>✍️ 写文章</a>
        <a href="{{ route('dashboard') }}" class="block py-2 text-sm {{ request()->routeIs('dashboard') ? 'text-primary font-medium' : 'text-text-secondary' }} px-3">Dashboard</a>
        <a href="{{ route('profile.edit') }}" class="block py-2 text-sm text-text-secondary px-3">Profile</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="block w-full text-left py-2 text-sm text-text-secondary px-3">Log Out</button>
        </form>
      @endauth
    </div>
  </div>
</nav>
