<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        {{-- 主题切换 --}}
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
    <body class="font-sans antialiased text-text bg-bg min-h-screen">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 pb-8 px-4">
            {{-- Logo --}}
            <div class="mb-5">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-white text-sm font-bold">M</span>
                    <span class="text-base font-bold text-text">My Blog</span>
                </a>
            </div>

            {{-- Card --}}
            <div class="w-full sm:max-w-[560px] mx-4 px-8 py-8 sm:px-10 sm:py-9 bg-surface-2 border border-border overflow-hidden rounded-2xl" style="box-shadow: 0 2px 16px -6px rgba(44, 42, 40, 0.06);">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
