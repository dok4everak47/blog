<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- 每页可通过 @section('title') 覆盖，默认用站点名 --}}
    <title>@yield('title', config('app.name', 'My Blog'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-bg min-h-screen text-text flex flex-col">
    <x-nav />

    <div class="flex-1">
        @yield('content')
    </div>

    <x-footer />
</body>
</html>
