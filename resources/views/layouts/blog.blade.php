<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- 每页可通过 @section('title') 覆盖，默认用站点名 --}}
    <title>@yield('title', config('app.name', 'My Blog'))</title>

    {{-- SEO Meta（子页面通过 @section('seo') 填充） --}}
    @yield('seo')
    <meta name="robots" content="index, follow">
    <link rel="canonical" content="{{ url()->current() }}">

    {{-- 主题切换：内联执行避免闪烁 --}}
    <script>
        (function() {
            var stored = localStorage.getItem('theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (stored === 'dark' || (!stored && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-bg min-h-screen text-text flex flex-col">
    <x-nav />

    <div class="flex-1">
        @yield('content')
    </div>

    <x-footer />

    {{-- 返回顶部按钮 --}}
    <button id="back-to-top"
            class="fixed bottom-6 right-6 z-50 w-10 h-10 rounded-full bg-surface-2 border border-border shadow-md flex items-center justify-center text-text-secondary hover:text-primary hover:border-primary transition-all duration-200 opacity-0 pointer-events-none translate-y-2"
            aria-label="返回顶部">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
    </button>
    <script>
        (function() {
            var btn = document.getElementById('back-to-top');
            if (!btn) return;
            window.addEventListener('scroll', function() {
                if (window.scrollY > 400) {
                    btn.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-2');
                } else {
                    btn.classList.add('opacity-0', 'pointer-events-none', 'translate-y-2');
                }
            }, { passive: true });
            btn.addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
