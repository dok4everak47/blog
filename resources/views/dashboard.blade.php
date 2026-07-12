<x-app-layout>
    <x-slot name="header">
        <div class="max-w-6xl mx-auto">
            <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-1">Dashboard</p>
            <h2 class="font-bold text-xl text-text leading-tight">
                {{ __('后台管理') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-bg">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Welcome --}}
            <div class="rounded-2xl border border-border bg-white p-6 sm:p-8">
                <h1 class="text-2xl font-bold text-text">
                    你好，{{ Auth::user()->name }} 👋
                </h1>
                <p class="mt-2 text-text-secondary">
                    欢迎回到 Dashboard，从这里管理你的博客内容。
                </p>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-2xl border border-border bg-white p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-text-secondary">文章总数</p>
                            <p class="text-3xl font-bold text-text mt-1">{{ $notesCount }}</p>
                        </div>
                        <span class="text-3xl">📝</span>
                    </div>
                </div>
                <div class="rounded-2xl border border-border bg-white p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-text-secondary">分类数</p>
                            <p class="text-3xl font-bold text-text mt-1">{{ $categoriesCount }}</p>
                        </div>
                        <span class="text-3xl">📁</span>
                    </div>
                </div>
                <div class="rounded-2xl border border-border bg-white p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-text-secondary">标签数</p>
                            <p class="text-3xl font-bold text-text mt-1">{{ $tagsCount }}</p>
                        </div>
                        <span class="text-3xl">🏷️</span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="rounded-2xl border border-border bg-white p-6 sm:p-8">
                <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-4">快捷操作</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('notes.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-primary-hover hover:shadow-md hover:-translate-y-px active:translate-y-0 active:scale-[0.98]">
                        ✍️ 写文章
                    </a>
                    <a href="{{ route('profile.edit') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-border bg-surface px-5 py-2.5 text-sm font-medium text-text transition-all duration-200 hover:bg-white hover:border-border-strong hover:-translate-y-px">
                        ⚙️ 个人资料
                    </a>
                    <a href="{{ route('home') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-border bg-surface px-5 py-2.5 text-sm font-medium text-text transition-all duration-200 hover:bg-white hover:border-border-strong hover:-translate-y-px">
                        🏠 查看博客
                    </a>
                </div>
            </div>

            {{-- Recent Notes --}}
            <div class="rounded-2xl border border-border bg-white p-6 sm:p-8">
                <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-4">最近文章</p>

                @if ($notes->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($notes as $note)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-surface border border-border hover:border-border-strong transition">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('notes.show', $note) }}"
                                            class="text-sm font-medium text-text hover:text-primary transition">
                                            {{ \Illuminate\Support\Str::limit($note->title, 40) }}
                                        </a>
                                        @if ($note->isDraft())
                                            <span class="shrink-0 rounded-full bg-gold-light px-2 py-0.5 text-xs font-medium text-gold-hover">草稿</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-text-secondary mt-0.5">
                                        {{ $note->created_at->format('Y-m-d') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 ml-4">
                                    <a href="{{ route('notes.edit', $note) }}"
                                        class="text-xs text-primary hover:text-primary-hover transition">编辑</a>
                                    <form action="{{ route('notes.destroy', $note) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:text-red-700 transition"
                                            onclick="return confirm('确定要删除这篇文章吗？')">删除</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-text-secondary text-center py-6">
                        还没有文章，<a href="{{ route('notes.create') }}" class="text-primary hover:text-primary-hover">去写一篇吧</a>
                    </p>
                @endif
            </div>

            {{-- Flash --}}
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                    class="fixed bottom-6 right-6 rounded-xl bg-primary px-6 py-3 text-sm font-medium text-white shadow-md">
                    {{ session('success') }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
