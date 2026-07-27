# 风格迁移代码

> 按你之前的习惯，我把完整代码片段放在这里，你逐文件替换即可。如果你想让我直接改文件，告诉我一声。

## 1. `vite.config.js`（增加 700 字重）

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600, 700],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
```

## 2. `resources/css/app.css`

```css
@import 'tailwindcss';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';

@theme {
    --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji',
        'Segoe UI Symbol', 'Noto Color Emoji';

    /* High Scalability 风格色板 */
    --color-primary: #5AC596;
    --color-primary-hover: #4DB583;
    --color-text: #111827;
    --color-text-secondary: #6B7280;
    --color-surface: #F9FAFB;
    --color-border: #E5E7EB;
}
```

## 3. `resources/views/components/nav.blade.php`

```blade
<nav class="bg-white border-b border-border">
  <div class="max-w-4xl mx-auto px-4 h-16 flex items-center justify-between">
    {{-- 左侧页面链接 --}}
    <div class="flex items-center gap-6">
      <a href="{{ url('/') }}"
        class="text-sm font-medium text-text-secondary hover:text-text transition {{ request()->is('/') ? 'text-text' : '' }}">
        首页
      </a>
      <a href="{{ url('profile') }}"
        class="text-sm font-medium text-text-secondary hover:text-text transition {{ request()->is('profile') ? 'text-text' : '' }}">
        Profile
      </a>
      <a href="{{ url('contact') }}"
        class="text-sm font-medium text-text-secondary hover:text-text transition {{ request()->is('contact') ? 'text-text' : '' }}">
        Contact us
      </a>
    </div>

    {{-- 中间 Logo --}}
    <a href="{{ url('/') }}" class="text-xl font-bold text-text tracking-tight">
      My Blog
    </a>

    {{-- 右侧操作 --}}
    <div class="flex items-center gap-4">
      <a href="#" class="text-sm font-medium text-text-secondary hover:text-text transition">
        Sign in
      </a>
      <a href="#"
        class="rounded-full bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-hover transition">
        Subscribe
      </a>
    </div>
  </div>
</nav>
```

## 4. `resources/views/home.blade.php`（首页）

```blade
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>我的 Blog</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white min-h-screen text-text">
  <x-nav />

  <main class="max-w-4xl mx-auto px-4 py-12">
    {{-- 页面标题区 --}}
    <section class="text-center mb-16">
      <h1 class="text-4xl font-bold tracking-tight mb-3">My Blog</h1>
      <p class="text-lg text-text-secondary">记录学习与生活的每一刻</p>
      <div class="mt-6">
        <a href="{{ route('notes.index') }}"
          class="inline-flex items-center gap-2 rounded-full border border-border bg-white px-5 py-2.5 text-sm font-medium text-text hover:bg-surface transition">
          ✍️ 写笔记
        </a>
      </div>
    </section>

    {{-- 最近文章列表 --}}
    <section class="divide-y divide-border">
      @forelse ($notes as $note)
        <article class="py-8">
          <a href="{{ route('notes.show', $note) }}" class="group flex flex-col sm:flex-row gap-6">
            {{-- 缩略图占位 --}}
            <div class="shrink-0 w-full sm:w-40 h-40 sm:h-24 rounded-lg bg-surface border border-border overflow-hidden flex items-center justify-center">
              <span class="text-2xl font-bold text-text-secondary/40">
                {{ strtoupper(substr($note->title, 0, 1)) }}
              </span>
            </div>

            <div class="flex-1 min-w-0">
              <h2 class="text-xl font-bold text-text group-hover:opacity-70 transition leading-snug">
                {{ $note->title }}
              </h2>
              <p class="mt-2 text-text-secondary line-clamp-2 leading-relaxed">
                {{ $note->content }}
              </p>
              <div class="mt-3 flex flex-wrap items-center gap-2 text-sm text-text-secondary">
                <span>By My Blog</span>
                <span>—</span>
                <span>{{ $note->created_at->format('d M Y') }}</span>
                @if ($note->category)
                  <span class="rounded-full border border-border bg-surface px-2.5 py-0.5 text-xs">
                    {{ $note->category->name }}
                  </span>
                @endif
              </div>
            </div>
          </a>
        </article>
      @empty
        <p class="py-12 text-center text-text-secondary">还没有笔记，去写一条吧！</p>
      @endforelse
    </section>

    {{-- 分类 + 标签 --}}
    <section class="mt-16 grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="border-t border-border pt-6">
        <h3 class="text-sm font-semibold mb-4">分类</h3>
        <div class="space-y-3">
          @foreach ($categories as $category)
            <div class="flex justify-between text-sm text-text-secondary">
              <span>{{ $category->name }}</span>
              <span>{{ $category->notes_count }} 篇</span>
            </div>
          @endforeach
        </div>
      </div>

      <div class="border-t border-border pt-6">
        <h3 class="text-sm font-semibold mb-4">标签</h3>
        <div class="flex flex-wrap gap-2">
          @foreach ($tags as $tag)
            <a href="{{ route('notes.index', ['tag' => $tag->id]) }}"
              class="rounded-full border border-border bg-surface px-3 py-1 text-xs text-text-secondary hover:border-primary hover:text-primary transition">
              {{ $tag->name }} ({{ $tag->notes_count }})
            </a>
          @endforeach
        </div>
      </div>
    </section>

    {{-- 页脚订阅区 --}}
    <footer class="mt-20 border-t border-border pt-12 text-center">
      <h2 class="text-3xl font-bold mb-2">My Blog</h2>
      <p class="text-text-secondary mb-6">记录学习与生活的每一刻</p>
      <form class="relative max-w-md mx-auto rounded-full border border-border bg-surface p-1"
        onsubmit="event.preventDefault();">
        <input type="email" placeholder="jamie@example.com"
          class="w-full bg-transparent pl-5 pr-28 py-2.5 text-sm outline-none placeholder:text-text-secondary">
        <button type="submit"
          class="absolute right-1 top-1 bottom-1 rounded-full bg-primary px-5 text-sm font-medium text-white hover:bg-primary-hover transition">
          Subscribe
        </button>
      </form>
    </footer>
  </main>
</body>
</html>
```

## 5. `resources/views/notes/index.blade.php`（写笔记页）

```blade
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>写笔记</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white min-h-screen text-text">
  <x-nav />

  <main class="min-h-[calc(100vh-64px)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-xl">
      <h1 class="text-2xl font-bold text-center mb-8">写一条新笔记</h1>

      <div class="border border-border rounded-2xl bg-white p-6 sm:p-8">
        <form action="{{ route('notes.store') }}" method="POST" class="space-y-5">
          @csrf

          <div>
            <label class="block text-sm font-medium text-text mb-1.5">标题</label>
            <input type="text" name="title" placeholder="给笔记起个名字..."
              class="w-full rounded-xl border border-border bg-surface px-4 py-2.5 text-sm outline-none placeholder:text-text-secondary focus:border-primary focus:ring-1 focus:ring-primary transition">
            @error('title')
              <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
            @enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-text mb-1.5">内容</label>
            <textarea name="content" placeholder="写下你的想法..." rows="5"
              class="w-full rounded-xl border border-border bg-surface px-4 py-2.5 text-sm outline-none placeholder:text-text-secondary focus:border-primary focus:ring-1 focus:ring-primary transition resize-none"></textarea>
            @error('content')
              <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
            @enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-text mb-1.5">分类</label>
            <select name="category_id"
              class="w-full rounded-xl border border-border bg-surface px-4 py-2.5 text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary transition">
              <option value="">不选分类</option>
              @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-text mb-2">标签</label>
            <div class="flex flex-wrap gap-3">
              @foreach ($tags as $tag)
                <label class="inline-flex items-center gap-1.5 text-sm text-text-secondary cursor-pointer">
                  <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                    class="h-4 w-4 rounded border-border text-primary focus:ring-primary">
                  {{ $tag->name }}
                </label>
              @endforeach
            </div>
          </div>

          <button type="submit"
            class="w-full rounded-full bg-primary py-2.5 text-sm font-medium text-white hover:bg-primary-hover transition">
            保存笔记
          </button>
        </form>
      </div>

      <a href="{{ url('/') }}"
        class="mt-6 block text-center text-sm text-text-secondary hover:text-text transition">
        ← 回首页
      </a>
    </div>
  </main>
</body>
</html>
```

## 6. `resources/views/notes/show.blade.php`（笔记详情页）

```blade
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $note->title }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white min-h-screen text-text">
  <x-nav />

  <main class="max-w-2xl mx-auto px-4 py-12">
    <article>
      <h1 class="text-3xl sm:text-4xl font-bold tracking-tight mb-4">{{ $note->title }}</h1>

      <div class="flex flex-wrap items-center gap-3 mb-8 text-sm text-text-secondary">
        <span>{{ $note->created_at->format('d M Y') }}</span>
        @if ($note->category)
          <span class="rounded-full border border-border bg-surface px-2.5 py-0.5 text-xs">
            {{ $note->category->name }}
          </span>
        @endif
      </div>

      <div class="border-t border-border pt-8">
        <p class="text-text leading-relaxed whitespace-pre-line">{{ $note->content }}</p>
      </div>
    </article>

    @if ($note->tags->isNotEmpty())
      <div class="mt-8 flex flex-wrap gap-2">
        @foreach ($note->tags as $tag)
          <a href="{{ route('notes.index', ['tag' => $tag->id]) }}"
            class="rounded-full border border-border bg-surface px-3 py-1 text-xs text-text-secondary hover:border-primary hover:text-primary transition">
            {{ $tag->name }}
          </a>
        @endforeach
      </div>
    @endif

    <div class="mt-10 flex flex-wrap items-center gap-4 text-sm">
      <a href="{{ route('notes.index') }}" class="text-text-secondary hover:text-text transition">← 返回列表</a>
      <a href="{{ route('notes.edit', $note) }}" class="text-primary hover:text-primary-hover transition">编辑</a>
      <form action="{{ route('notes.destroy', $note) }}" method="POST" class="inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-600 hover:text-red-700 transition">删除</button>
      </form>
    </div>
  </main>
</body>
</html>
```

## 7. `resources/views/profile.blade.php`

```blade
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>About me</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white min-h-screen text-text">
  <x-nav />

  <main class="max-w-2xl mx-auto px-4 py-12">
    <div class="flex items-center gap-6 mb-10">
      <div class="w-24 h-24 rounded-full bg-primary flex items-center justify-center text-white text-3xl font-bold">
        B
      </div>
      <div>
        <h1 class="text-3xl font-bold">ブロガー</h1>
        <p class="text-text-secondary mt-1">Be studing Laravel... Loving noting</p>
      </div>
    </div>

    <section class="border-t border-border pt-8 mb-8">
      <h2 class="text-lg font-semibold mb-3">About</h2>
      <p class="text-text-secondary leading-relaxed">
        Studing Laravel from 0, 用这个 Blog 记录自己的学习过程...
      </p>
    </section>

    <section class="border-t border-border pt-8 mb-8">
      <h2 class="text-lg font-semibold mb-3">正在学习</h2>
      <div class="flex flex-wrap gap-2">
        <span class="rounded-full border border-border bg-surface px-3 py-1 text-sm text-text-secondary">Laravel</span>
        <span class="rounded-full border border-border bg-surface px-3 py-1 text-sm text-text-secondary">Blade</span>
        <span class="rounded-full border border-border bg-surface px-3 py-1 text-sm text-text-secondary">Tailwind</span>
        <span class="rounded-full border border-border bg-surface px-3 py-1 text-sm text-text-secondary">Eloquent</span>
        <span class="rounded-full border border-border bg-surface px-3 py-1 text-sm text-text-secondary">Vite</span>
      </div>
    </section>

    <section class="border-t border-border pt-8">
      <h2 class="text-lg font-semibold mb-3">联系方式</h2>
      <div class="space-y-2 text-text-secondary">
        <p>邮箱: hello@example.com</p>
        <p>Github: github.com/username</p>
      </div>
    </section>
  </main>
</body>
</html>
```

## 8. `resources/views/contact.blade.php`

```blade
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>联系我们</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white min-h-screen text-text">
  <x-nav />

  <main class="flex items-center justify-center min-h-[calc(100vh-64px)] px-4">
    <div class="text-center max-w-md">
      <h1 class="text-3xl font-bold mb-4">联系我们</h1>
      <p class="text-text-secondary mb-6">邮箱: hello@example.com</p>
      <a href="{{ url('/') }}"
        class="inline-block rounded-full border border-border bg-white px-5 py-2.5 text-sm font-medium text-text hover:bg-surface transition">
        ← 回首页
      </a>
    </div>
  </main>
</body>
</html>
```

## 9. 跑起来

改完后运行：

```bash
npm run dev
```

如果 Vite 提示字体缓存问题，可以清一下：

```bash
php artisan view:clear
rm -rf node_modules/.vite
npm run dev
```
