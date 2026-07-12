<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

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

        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/about-editor.js'])
        <link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}">
    </head>
    <body class="font-sans antialiased text-text bg-bg min-h-screen flex flex-col">
        <x-nav />

        <main class="flex-1">
            @isset($header)
                <header class="bg-surface-2 border-b border-border">
                    <div class="max-w-6xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            {{ $slot }}
        </main>

        <x-footer />
    </body>
</html>
