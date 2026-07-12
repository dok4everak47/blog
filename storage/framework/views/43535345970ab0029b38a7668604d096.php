<?php $__env->startSection('title', '关于 · My Blog'); ?>

<?php $__env->startSection('seo'); ?>
<meta name="description" content="关于我 — <?php echo e(config('app.name', 'My Blog')); ?>">
<meta property="og:type" content="profile">
<meta property="og:title" content="关于">
<meta property="og:url" content="<?php echo e(route('about')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $markdown = \App\Models\SiteSetting::get('about_markdown', '');
    $html = $markdown ? \Illuminate\Support\Str::markdown($markdown) : '';
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
  <div class="flex gap-10">

    
    <div class="flex-1 min-w-0">

      
      <header class="mb-10">
        <h1 class="text-2xl sm:text-3xl font-bold text-text flex items-center gap-2.5">
          <span class="text-primary">◆</span>
          关于·自我
        </h1>
      </header>

      
      <?php if($html): ?>
      <article class="about-content text-[15px] leading-[1.85] text-text prose-custom">
        <?php echo $html; ?>

      </article>
      <?php else: ?>
      <p class="text-text-muted italic py-8">暂无内容…</p>
      <?php endif; ?>

      
      <hr class="border-dashed border-border my-10">

     
      <aside class="rounded-lg border-l-[3px] border-primary bg-surface-2/60 p-5 sm:p-6 mb-10">
        <p class="text-sm leading-relaxed mb-2">
          <span class="font-medium text-text">本文作者：</span>
          <span class="text-text-secondary">Dok4ever</span>
        </p>
        <p class="text-sm leading-relaxed mb-2">
          <span class="font-medium text-text">本文链接：</span>
          <a href="<?php echo e(url()->current()); ?>" class="text-primary hover:text-primary-hover transition break-all"><?php echo e(url()->current()); ?></a>
        </p>
        <p class="text-sm leading-relaxed text-text-secondary">
          <span class="font-medium text-text">版权声明：</span>
          本站所有文章除特别声明外，均采用
          <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/" target="_blank" rel="noopener noreferrer"
             class="text-primary hover:text-primary-hover transition">CC BY-NC-SA</a>
          许可协议。
        </p>
      </aside>

      
      <?php
        $latestNote = \App\Models\Note::published()->with('category')->latest()->first();
      ?>
      <?php if($latestNote): ?>
      <a href="<?php echo e(route('notes.show', $latestNote)); ?>"
         class="group block rounded-2xl overflow-hidden relative h-48 sm:h-56 hover:-translate-y-0.5 transition-transform duration-300">
        <?php if($latestNote->thumbnail_url || $latestNote->cover_image_url): ?>
          <img src="<?php echo e($latestNote->thumbnail_url ?: $latestNote->cover_image_url); ?>"
               alt="" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-black/25 to-transparent"></div>
        <?php else: ?>
          <div class="absolute inset-0 bg-gradient-to-br from-sage/70 via-sage to-sage-light"></div>
        <?php endif; ?>

        <div class="relative z-10 h-full flex flex-col justify-end p-6 sm:p-8">
          <p class="text-[11px] font-medium tracking-[0.2em] text-white/60 uppercase mb-2">Latest Post</p>
          <h3 class="text-lg sm:text-xl font-bold text-white group-hover:text-sage-light/90 transition">
            <?php echo e($latestNote->title); ?>

          </h3>
        </div>
      </a>
      <?php endif; ?>
    </div>

    
    <aside class="hidden lg:block w-52 shrink-0">
      <div class="sticky top-24">
        <p class="text-xs font-medium tracking-[0.15em] text-text-muted uppercase mb-4">页面目录</p>
        <nav id="about-toc" class="space-y-1">
          
        </nav>
      </div>
    </aside>

  </div>
</div>


<script>
(function() {
    var tocContainer = document.getElementById('about-toc');
    if (!tocContainer) return;
    var article = document.querySelector('.about-content');
    if (!article) return;

    var headings = article.querySelectorAll('h2, h3');
    var iconMap = { H2: '◆', H3: '└' };

    headings.forEach(function(h, i) {
        if (!h.id) {
            h.id = 'about-heading-' + i;
        }
        var a = document.createElement('a');
        a.href = '#' + h.id;
        a.className = 'flex items-center gap-2.5 text-sm py-2 px-3 rounded-lg border-l-[3px] border-transparent text-text-secondary hover:text-text hover:border-border-strong hover:bg-surface-2 transition';
        a.innerHTML = '<span>' + (iconMap[h.tagName] || '•') + '</span> ' + h.textContent.trim();
        a.addEventListener('click', function() {
            tocContainer.querySelectorAll('a').forEach(function(el) {
                el.classList.remove('border-primary', 'bg-primary-light/40', 'text-primary', 'font-medium');
                el.classList.add('border-transparent');
            });
            this.classList.add('border-primary', 'bg-primary-light/40', 'text-primary', 'font-medium');
            this.classList.remove('border-transparent');
        });
        tocContainer.appendChild(a);
    });
    var first = tocContainer.querySelector('a');
    if (first) first.click();
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.blog', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Volumes/T7/Project/blog/resources/views/profile.blade.php ENDPATH**/ ?>