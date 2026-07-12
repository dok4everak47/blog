@extends('layouts.blog')

@section('title', '维护中 · My Blog')

@section('content')
    <main class="flex items-center justify-center min-h-[calc(100vh-64px)] px-4 py-16">
        <div class="text-center max-w-md">
            <div class="w-16 h-16 rounded-2xl bg-gold-light flex items-center justify-center mx-auto mb-6">
                <span class="text-2xl font-bold text-gold">503</span>
            </div>
            <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-2">Maintenance</p>
            <h1 class="text-3xl font-bold mb-4">系统维护中</h1>
            <p class="text-text-secondary mb-8">博客正在维护，请稍后再来访问。</p>
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-primary-hover hover:shadow-md hover:-translate-y-px active:translate-y-0 active:scale-[0.98]">
                ← 回首页
            </a>
        </div>
    </main>
@endsection
