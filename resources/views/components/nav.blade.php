<nav x-data="{ open: false }" class="sticky top-0 z-50 bg-surface-2/70 backdrop-blur-md border-b border-border">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      {{-- Left: Logo + public links --}}
      <div class="flex items-center gap-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
          <span class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-white text-sm font-bold">M</span>
          <span class="text-base font-bold text-text">My Blog</span>
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

      {{-- 中间：搜索框（桌面端） --}}
      <form action="{{ route('search') }}" method="GET" class="relative hidden sm:block flex-1 max-w-xs">
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="搜索文章…"
               class="w-full rounded-lg border border-border bg-surface px-3 py-1.5 pl-9 text-sm text-text outline-none transition focus:border-primary focus:bg-surface-2 focus:ring-2 focus:ring-primary/10">
        <svg class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-text-muted pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
      </form>

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
          <a href="{{ route('register') }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-hover transition">Register</a>
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
        <a href="{{ route('register') }}" class="block py-2 text-sm text-primary font-medium px-3">Register</a>
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
