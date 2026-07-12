<nav x-data="{ open: false }" class="sticky top-0 z-50 bg-white/70 backdrop-blur-md border-b border-border">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-white text-sm font-bold">M</span>
                    <span class="text-base font-bold text-text">My Blog</span>
                </a>

                <div class="hidden sm:flex items-center gap-5 ml-4">
                    <a href="{{ route('home') }}"
                        class="text-sm transition {{ request()->routeIs('home') ? 'text-primary font-medium' : 'text-text-secondary hover:text-primary' }}">
                        首页
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

            <div class="hidden sm:flex sm:items-center gap-3">
                @guest
                    <a href="{{ route('login') }}"
                        class="text-sm text-text-secondary hover:text-primary transition px-3 py-2">
                        Login
                    </a>
                    <a href="{{ route('register') }}"
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-hover transition">
                        Register
                    </a>
                @endguest

                @auth
                    <a href="{{ route('dashboard') }}"
                        class="text-sm transition {{ request()->routeIs('dashboard') ? 'text-primary font-medium' : 'text-text-secondary hover:text-primary' }} px-3 py-2">
                        Dashboard
                    </a>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-text-secondary bg-white hover:text-primary focus:outline-none transition">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-lg text-text-secondary hover:text-primary hover:bg-surface transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-border bg-white">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <a href="{{ route('home') }}" class="block py-2 text-sm rounded-lg px-3 {{ request()->routeIs('home') ? 'text-primary font-medium bg-surface' : 'text-text-secondary' }}">首页</a>
            <a href="{{ route('about') }}" class="block py-2 text-sm rounded-lg px-3 {{ request()->routeIs('about') ? 'text-primary font-medium bg-surface' : 'text-text-secondary' }}">关于</a>
            <a href="{{ route('contact') }}" class="block py-2 text-sm rounded-lg px-3 {{ request()->routeIs('contact') ? 'text-primary font-medium bg-surface' : 'text-text-secondary' }}">Contact</a>

            @guest
                <a href="{{ route('login') }}" class="block py-2 text-sm text-text-secondary px-3">Login</a>
                <a href="{{ route('register') }}" class="block py-2 text-sm text-primary font-medium px-3">Register</a>
            @endguest

            @auth
                <a href="{{ route('dashboard') }}" class="block py-2 text-sm rounded-lg px-3 {{ request()->routeIs('dashboard') ? 'text-primary font-medium bg-surface' : 'text-text-secondary' }}">Dashboard</a>
                <a href="{{ route('profile.edit') }}" class="block py-2 text-sm text-text-secondary px-3">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left py-2 text-sm text-text-secondary px-3">Log Out</button>
                </form>
            @endauth
        </div>
    </div>
</nav>
