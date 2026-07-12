<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>About me</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-bg min-h-screen text-text">
  <x-nav />

  <main class="max-w-2xl mx-auto px-4 sm:px-6 py-16 sm:py-20">
    <div class="flex items-center gap-6 mb-12">
      <div class="w-20 h-20 rounded-2xl bg-primary flex items-center justify-center text-white text-2xl font-bold">
        B
      </div>
      <div>
        <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-1">Blogger</p>
        <h1 class="text-3xl font-bold">ブロガー</h1>
        <p class="text-text-secondary mt-1">Be studying Laravel... Loving noting</p>
      </div>
    </div>

    <section class="rounded-2xl border border-border bg-white p-6 sm:p-8 mb-6">
      <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-3">About</p>
      <p class="text-text-secondary leading-relaxed">
        Studing Laravel from 0, 用这个 Blog 记录自己的学习过程...
      </p>
    </section>

    <section class="rounded-2xl border border-border bg-white p-6 sm:p-8 mb-6">
      <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-3">Learning</p>
      <div class="flex flex-wrap gap-2">
        <span class="rounded-full bg-surface border border-border px-3 py-1 text-sm text-text-secondary">Laravel</span>
        <span class="rounded-full bg-surface border border-border px-3 py-1 text-sm text-text-secondary">Blade</span>
        <span class="rounded-full bg-surface border border-border px-3 py-1 text-sm text-text-secondary">Tailwind</span>
        <span class="rounded-full bg-surface border border-border px-3 py-1 text-sm text-text-secondary">Eloquent</span>
        <span class="rounded-full bg-surface border border-border px-3 py-1 text-sm text-text-secondary">Vite</span>
      </div>
    </section>

    <section class="rounded-2xl border border-border bg-white p-6 sm:p-8">
      <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-3">Contact</p>
      <div class="space-y-2 text-text-secondary">
        <p>邮箱: hello@example.com</p>
        <p>Github: github.com/username</p>
      </div>
    </section>
  </main>
</body>
</html>
