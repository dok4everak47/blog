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
    <link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}">
</head>

<body class="bg-bg min-h-screen text-text flex flex-col">
    <x-nav />

    <div class="flex-1">
        @yield('content')
    </div>

    <x-footer />
</body>
</html>
